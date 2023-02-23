<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Events;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener as PMListener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;
use Versai\ModerationPM\Database\Container\PlayerData;
use Versai\ModerationPM\Database\Container\Punishment;
use Versai\ModerationPM\Main;
use Versai\ModerationPM\Utilities\Utilities;
use function strtr;
use function substr;

class Listener implements PMListener{

    private Main $plugin;
    private string|null $staffChatChar;
    /** @var string[] $deviceIDs */
    private array $deviceIDs;

    /**
     * Listener constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        $this->staffChatChar = $plugin->getCommandConfig()->getNested('Staff Chat.Inverse Character');
    }

    public function onLogin(PlayerLoginEvent $event){
        $player = $event->getPlayer();
        $username = $player->getName();

        $clientData = $player->getPlayerInfo()->getExtraData();
        $deviceID = $clientData['DeviceId'];
        $this->deviceIDs[$username] = $deviceID;
        $this->plugin->getDeviceManager()->setDeviceOS($username, $clientData['DeviceOS']);
        $this->plugin->getDeviceManager()->setInputMode($username, $clientData['CurrentInputMode']);

        if(isset($this->deviceIDs[$player->getName()])){
            $name = $player->getName();
            $xuid = $player->getXuid();
            $deviceID = $this->deviceIDs[$name];
            $provider = $this->plugin->getProvider();
            $provider->asyncGetPlayer($name, $xuid, $deviceID, false, function (array $result) use ($provider, $player, $name, $xuid, $deviceID, $event): void {
                $playerData = PlayerData::fromDatabaseQuery($result);
                if ($playerData === null) {
                    $provider->asyncRegisterPlayer($name, $xuid, $deviceID, $player->getNetworkSession()->getIp(), function () use ($event): void {
                        $this->onLogin($event);
                    });
                }
            });
            $provider->asyncGetPlayer($name, $xuid, $deviceID,true, function (array $result) use ($player): void {
                foreach ($result as $playerData) {
                    $playerData = PlayerData::fromDatabaseQuery($playerData, PlayerData::NO_KEY);
                    if ($playerData !== null) {
                        $this->checkAllPunishments($player, $playerData);
                    }
                }
            });
        }
    }

    /**
     * @param Player $player
     * @param PlayerData $playerData
     */
    public function checkAllPunishments(Player $player, PlayerData $playerData): void{
        $provider = $this->plugin->getProvider();
        $id = $playerData->getID();
        $name = $playerData->getName();
        $this->plugin->getPlayerData()->set($playerData); // Assign player and id together in RAM
        $provider->asyncCheckPunished($id, Punishment::TYPE_BAN, function (array $result) use ($provider, $player, $id, $name): void {
            /** @var Punishment $punishment */
            $punishment = Punishment::fromDatabaseQuery($result, 0, Punishment::TYPE_BAN);
            if ($punishment !== null) {
                $until = $punishment->getUntil();
                if (Utilities::isStillPunished($until)) {
                    $message = $this->plugin->resolvePunishmentMessage(Punishment::TYPE_BAN, $punishment->getReason(), $until, $punishment->getStaffName());
                    $player->kick($message, "");
                    return;
                }
                $this->plugin->getProvider()->asyncRemovePunishment($id, Punishment::TYPE_BAN, $this->getOnDelete($name, Punishment::TYPE_BAN));
            }
            $provider->asyncCheckPunished($id, Punishment::TYPE_IP_BAN, function (array $result) use ($player, $id, $name): void {
                $punishment = null;
                foreach ($result as $key => $entry) {
                    $resultClone = $result;
                    /** @var Punishment $potentialPunishment
                     * @var Punishment|null $punishment
                     */
                    $potentialPunishment = Punishment::fromDatabaseQuery($resultClone, $key, Punishment::TYPE_IP_BAN);
                    if ($potentialPunishment !== null and
                        ($potentialPunishment->getUntil() === Punishment::FOREVER) || ($punishment === null || $potentialPunishment->getUntil() > $punishment->getUntil()))
                        $punishment = $potentialPunishment;
                }
                if ($punishment !== null) {
                    $until = $punishment->getUntil();
                    if (Utilities::isStillPunished($until)) {
                        $player->kick($this->plugin->resolvePunishmentMessage(Punishment::TYPE_IP_BAN, $punishment->getReason(), $until, $punishment->getStaffName()), "");
                        return;
                    }
                    $this->plugin->getProvider()->asyncRemovePunishment($id, Punishment::TYPE_IP_BAN, $this->getOnDelete($name, Punishment::TYPE_IP_BAN));
                }
            });
            $provider->asyncCheckPunished($id, Punishment::TYPE_FREEZE, function (array $result) use ($player, $id, $name): void {
                /** @var Punishment $punishment */
                $punishment = Punishment::fromDatabaseQuery($result, 0, Punishment::TYPE_FREEZE);
                if ($punishment !== null) {
                    $until = $punishment->getUntil();
                    if (Utilities::isStillPunished($until)) {
                        $this->plugin->getFrozen()->action($player);
                        if ($player->getNetworkSession()->isConnected())
                            $player->sendMessage($this->plugin->resolvePunishmentMessage(Punishment::TYPE_FREEZE, $punishment->getReason(), $until, $punishment->getStaffName()));
                    } else
                        $this->plugin->getProvider()->asyncRemovePunishment($id, Punishment::TYPE_IP_BAN, $this->getOnDelete($name, Punishment::TYPE_FREEZE));
                }
            });
            $provider->asyncCheckPunished($id, Punishment::TYPE_MUTE, function (array $result) use ($player, $id, $name): void {
                /** @var Punishment $punishment */
                $punishment = Punishment::fromDatabaseQuery($result, 0, Punishment::TYPE_MUTE);
                if ($punishment !== null) {
                    $until = $punishment->getUntil();
                    if (Utilities::isStillPunished($until)) {
                        $this->plugin->getMuted()->action($player);
                        if ($player->getNetworkSession()->isConnected())
                            $player->sendMessage($this->plugin->resolvePunishmentMessage(Punishment::TYPE_MUTE, $punishment->getReason(), $until, $punishment->getStaffName()));
                        return;
                    }
                    $this->plugin->getProvider()->asyncRemovePunishment($id, Punishment::TYPE_MUTE, $this->getOnDelete($name, Punishment::TYPE_MUTE));
                }
            });
        });
    }

    public function onPreLogin(PlayerPreLoginEvent $event){
        // I think this fixes it LMAO
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event): void{
        $name = $event->getPlayer()->getName();
        if($this->plugin->getStaffChat()->isInStaffChat($event->getPlayer())) {
            $this->plugin->getStaffChat()->removeFromStaffChat($event->getPlayer());
        }
        $this->plugin->getPlayerData()->unset($name);
        unset($this->deviceIDs[$name]);
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function onTalk(PlayerChatEvent $event): void{
        $player = $event->getPlayer();

        if ($this->plugin->getMuted()->checkState($player)){
            $event->cancel();
            return;
        }

        $msg = $event->getMessage();

        if ($this->plugin->getConfig()->getNested('Staff Chat.Enabled')){
            $toggledStaffChat = $this->plugin->getStaffChatToggled();
            $staffChat = $this->plugin->getStaffChat();

            if ($player->hasPermission('moderation.staff_chat')) {
                $staffChat->addToStaffChat($player);
            }else {
                $staffChat->removeFromStaffChat($player);
            }
            if ($staffChat->isInStaffChat($player)){
                if ($msg[0] === $this->staffChatChar){
                    $msg = substr($msg, 1);
                    if ($toggledStaffChat->checkState($player)) {
                        $event->setMessage($msg);
                    }else{
                        $staffChat->sendMessage($player, $msg);
                        $event->cancel();
                    }
                }elseif ($toggledStaffChat->checkState($player)){
                    $staffChat->sendMessage($player, $msg);
                    $event->cancel();
                }
            }
        }
    }

    /**
     * @param PlayerMoveEvent $event
     */
    public function onMove(PlayerMoveEvent $event): void{
        $player = $event->getPlayer();
        if ($this->plugin->getFrozen()->checkState($player))
            $player->setImmobile();
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onTap(EntityDamageEvent $event): void{
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            $player = $event->getEntity();
            $tapPunish = $this->plugin->getTapPunishUsers();
            if ($damager instanceof Player && $player instanceof Player && $tapPunish->checkState($damager) !== null) {
                $type = $this->plugin->getTapPunishUsers()->checkState($damager);
                $command = $this->plugin->getProvider()
                    ->resolveType($type, 'ban {player}', 'ban-ip {player}', 'mute {player}', 'freeze {player}', 'kick {player}', false);
                if ($command !== null) {
                    $event->cancel();
                    $damager->getServer()->dispatchCommand($damager, strtr($command, ['{player}' => $player->getName()]));
                    $tapPunish->reverseAction($damager);
                }
            }
        }
    }

    /**
     * @param EntityTeleportEvent $event
     * @priority LOWEST
     */
    public function onTeleport(EntityTeleportEvent $event) : void{
        $entity = $event->getEntity();
        if($entity instanceof Player && $this->plugin->getFrozen()->checkState($entity)){
            $event->cancel();
        }
    }

    /**
     * @param PlayerInteractEvent $event
     * @priority LOWEST
     */
    public function onInteract(PlayerInteractEvent $event) : void{
        $player = $event->getPlayer();
        if($this->plugin->getFrozen()->checkState($player)){
            $event->cancel();
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     * @priority LOWEST
     */
    public function onDamage(EntityDamageByEntityEvent $event) : void{
        $damaged = $event->getEntity();
        $damager = $event->getDamager();
        if($damaged instanceof Player && $damager instanceof Player){
            if($this->plugin->getFrozen()->checkState($damaged) || $this->plugin->getFrozen()->checkState($damager)){
                $event->cancel();
            }
        }
    }

    public function onCommand(CommandEvent $event) : void{
        $sender = $event->getSender();
        if($sender instanceof Player && $this->plugin->getMuted()->checkState($sender)){
            $list = ["msg", "w", "whisper", "tell"];
            $command = explode(" ", $event->getCommand())[0];
            if(in_array($command, $list)) {
                $event->cancel();
            }
        }
    }

    /**
     * @param $name
     * @param $type
     * @return callable
     */
    private function getOnDelete($name, $type): callable{
        return function (int $rows) use ($name, $type): void {
            if ($rows !== 0) {
                $expiredMsg = $this->plugin->getProvider()->typeToString($type, false) . ' expired!';
                $this->plugin->getLogger()->info("$name's " . $expiredMsg);
            }
        };
    }

    /**
     * @return array
     */
    public function getDeviceIDs(): array{
        return $this->deviceIDs;
    }
}
