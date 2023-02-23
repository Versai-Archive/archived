<?php
declare(strict_types=1);

namespace Versai\miscellaneous\events;

use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener as PMListener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\miscellaneous\Constants;
use Versai\miscellaneous\Miscellaneous;
use xenialdan\WarpUI\Loader;
use function count;
use function microtime;
use function round;
use function str_replace;

class Listener implements PMListener {

    private Miscellaneous $plugin;
    /** @var string[] $lastMessage */
    private array $lastMessage;
    /** @var float[] $timing */
    private array $timing;

    public function __construct(Miscellaneous $plugin){
        $this->plugin = $plugin;
    }

    /**
     * Always Spawn
     * @param PlayerJoinEvent $event
     * @priority HIGHEST
     */
    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();

        $server = $this->plugin->getServer();

        if($player->isClosed()) {
            return;
        }

        if(!$player->hasPermission(Constants::JOIN_WHEN_FULL) && count($server->getOnlinePlayers()) >= $server->getMaxPlayers() - Constants::RESERVED_SLOTS) {
            $player->kick('The server is full! Buy a rank to get access to the reserved slots!');
        }

        if ($player->hasPermission(Constants::JOIN_MSG)) {
            $event->setJoinMessage("");
            return;
        }

        $server = $this->plugin->getServer();
        $event->setJoinMessage(str_replace(["{player}", "{online}", "{max}"], [$player->getName(), count($server->getOnlinePlayers()), $server->getMaxPlayers()], $this->plugin->config["join-message"]));

        /** @var Location $warps */
        $warp = Loader::getWarp("spawn");
        if($warp !== null) {
            $player->teleport($warp);
        }
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();

        if ($player->hasPermission(Constants::LEAVE_MSG)) {
            $event->setQuitMessage("");
            return;
        }

        $server = $this->plugin->getServer();
        $event->setQuitMessage(str_replace(["{player}", "{online}", "{max}"], [$player->getName(), count($server->getOnlinePlayers()), $server->getMaxPlayers()], $this->plugin->config["leave-message"]));
    }

    /**
     * @param PlayerCommandPreprocessEvent $event
     */
    public function onChat(PlayerCommandPreprocessEvent $event): void{
        $event->setMessage(str_replace(['*shrug*'], [Constants::SHRUGGIE], $event->getMessage()));

        $config = $this->plugin->getConfig()->getAll();
        $player = $event->getPlayer();
        $msg = $event->getMessage();
        $id = $player->getId();
        $time = microtime(true);
        $cooldown = $config['Cooldown'] ?? 0;
        if($time - ($this->timing[$id] ?? 0) < $cooldown && $config['Enable-Cooldown'] && (!$player->hasPermission(Constants::CHAT_PROTECT) || !$player->hasPermission(Constants::CHAT_BYPASS_COOLDOWN))) {
            $player->sendMessage(TextFormat::RED . "Sorry but you have to wait at least $cooldown seconds to talk!");
            $event->cancel();
            return;
        }
        $this->timing[$id] = $time;
        if(isset($this->lastMessage[$id]) && $msg === $this->lastMessage[$id] && $config['Enable-Block-Repeat-Messages'] && !$player->hasPermission(Constants::CHAT_BYPASS_MESSAGE)) {
            $player->sendMessage(TextFormat::RED . "You can't do the same command or message twice in a row!");
            $event->cancel();
        }
        $this->lastMessage[$id] = $msg;
    }

    /**
     * Remove all effects
     * @param EntityTeleportEvent $event
     */
    public function onLevelChange(EntityTeleportEvent $event): void {
        $entity = $event->getEntity();
        $to = $event->getTo();
        $from = $event->getFrom();
        if($to->getWorld()->getFolderName() !== $from->getWorld()->getFolderName()) {
            if ($entity instanceof Player) {
                $entity->getEffects()->clear();
            }
        }
    }

    /**
     * @param QueryRegenerateEvent $event
     */
    public function onQuery(QueryRegenerateEvent $event): void {
        $event->getQueryInfo()->setMaxPlayerCount($this->plugin->getServer()->getMaxPlayers() + Constants::RESERVED_SLOTS);
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onDamage(EntityDamageEvent $event): void {
        $player = $event->getEntity();
        if ($player instanceof Player && $event->getCause() === EntityDamageEvent::CAUSE_VOID) {
            /** @var Location $warps */
            $warp = Loader::getWarp("spawn");
            if($warp !== null) {
                $player->teleport($warp);
            }
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();

        if ($cause instanceof EntityDamageByEntityEvent) {
            $killer = $cause->getDamager();
            if ($killer instanceof Player) {
                $event->setDeathMessage(str_replace(["{player}", "{killer}", "{health}"], [$player->getDisplayName(), $killer->getDisplayName(), round($killer->getHealth(), 1)], $this->plugin->config["kill-messages"][\array_rand($this->plugin->config["kill-messages"])]));
            }
        } else {

            if($cause === null) {
                return;
            }

            switch ($cause->getCause()) {

                case EntityDamageEvent::CAUSE_CONTACT:
                case EntityDamageEvent::CAUSE_PROJECTILE:
                    $msg = $this->plugin->config["death-contact"];
                    break;
                case EntityDamageEvent::CAUSE_SUFFOCATION:
                    $msg = $this->plugin->config["death-suffocation"];
                    break;
                case EntityDamageEvent::CAUSE_FALL:
                    $msg = $this->plugin->config["death-fall"];
                    break;
                case EntityDamageEvent::CAUSE_FIRE:
                case EntityDamageEvent::CAUSE_FIRE_TICK:
                    $msg = $this->plugin->config["death-fire"];
                    break;
                case EntityDamageEvent::CAUSE_LAVA:
                    $msg = $this->plugin->config["death-lava"];
                    break;
                case EntityDamageEvent::CAUSE_DROWNING:
                    $msg = $this->plugin->config["death-drown"];
                    break;
                case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
                case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
                    $msg = $this->plugin->config["death-explosion"];
                    break;
                case EntityDamageEvent::CAUSE_VOID:
                    $msg = $this->plugin->config["death-void"];
                    break;
                case EntityDamageEvent::CAUSE_SUICIDE:
                    $msg = $this->plugin->config["death-suicide"];
                    break;
                case EntityDamageEvent::CAUSE_MAGIC:
                    $msg = $this->plugin->config["death-magic"];
                    break;
                case EntityDamageEvent::CAUSE_STARVATION:
                    $msg = $this->plugin->config["death-starvation"];
                    break;
            }
        }
        if (isset($msg)) {
            $killer = $cause->getEntity();

            if ($killer instanceof Player)
                $event->setDeathMessage(str_replace(["{player}", "{killer}"], [$player->getDisplayName(), $killer->getDisplayName()], $msg));
            else
                $event->setDeathMessage(str_replace("{player}", $player->getDisplayName(), $msg));
        }
    }
}