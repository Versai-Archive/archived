<?php
declare(strict_types=1);

namespace Duo\vcosmetics\events;

use CortexPE\HRKChat\event\PlaceholderResolveEvent;
use pocketmine\event\Listener as PMListener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use Duo\vcosmetics\events\settings\CapeSetEvent;
use Duo\vcosmetics\events\settings\FlightSetEvent;
use Duo\vcosmetics\events\settings\FollowParticleSetEvent;
use Duo\vcosmetics\events\settings\HitParticleSetEvent;
use Duo\vcosmetics\Main;
use function array_values;

class Listener implements PMListener {

    private Main $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    /**
     * @priority HIGHEST
     */
    public function onLogin(PlayerLoginEvent $event){
        $player = $event->getPlayer();
        $data = $player->getPlayerInfo()->getExtraData();
        //$this->plugin->playerData->setXUID($player->getName(), $data['Waterdog_XUID']);
        $this->plugin->playerData->setXUID($player->getName(), $player->getXuid());
    }

    public function onJoin(PlayerJoinEvent $event): void{
        $provider = $this->plugin->getProvider();
        $player = $event->getPlayer();
        if($player instanceof Player){
            $sessionManager = $this->plugin->getSessionManager();
            $sessionManager->registerSession($player);

            $provider->asyncRegisterPlayerAll($player, static function() use ($player, $sessionManager, $provider){
                $provider->asyncGetPlayer($player, static function(array $result) use ($player, $sessionManager, $provider){
                    $session = $sessionManager->getSession($player);
                    $session->setData($result[0]);

                    // Hope this works lmfaooo

                    (new CapeSetEvent($player, $session->getCape()))->call();
                    (new FlightSetEvent($player, $session->getSpawnFlight()))->call();
                    (new HitParticleSetEvent($player, $session->getHitParticle()))->call();
                    (new FollowParticleSetEvent($player, $session->getFollowParticle()))->call();
                });
            });
        }
    }

    public function onQuit(PlayerQuitEvent $event): void{
        $player = $event->getPlayer();
        if($player instanceof Player){
            $session = $this->plugin->getSessionManager()->getSession($player);
            if($session !== null){
                $session->update();
                $this->plugin->getSessionManager()->unregisterSession($player);
            }
        }
    }

    public function onHRKResolve(PlaceholderResolveEvent $event){
        $placeHolder = $event->getPlaceholderName();
        $player = $event->getMember()->getPlayer();
        $session = $this->plugin->getSessionManager()->getSession($player);

        if($session == null){
            return;
        } else {
            $configTags = $this->plugin->tags;
            $clanTags = $this->plugin->clanTags;

            $customTag = $session->getCustomTag();
            $customTag = $customTag !== 'None' ? $customTag : "";
            $tag = $session->getTag();
            $tag = $tag !== 0 ? array_values($configTags)[$tag] : "";
            $clanTag = $session->getClanTag();
            $clanTag = $clanTag !== 0 ? array_values($clanTags)[$clanTag] : "";
            switch ($placeHolder) {
                case "player.customtag":
                    $event->setValue($customTag);
                    break;
                case "player.tag":
                    $event->setValue((string)$tag);
                    break;
                case "player.clantag":
                    $event->setValue((string)$clanTag);
                    break;
            }
        }
    }
}