<?php
declare(strict_types=1);

namespace Versai\vwarps\Events;

use pocketmine\event\Listener as PMListener;
use pocketmine\event\player\PlayerLoginEvent;
use Versai\vwarps\Main;

class Listener implements PMListener {

    private Main $plugin;

    /**
     * Listener constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onLogin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();
        $clientData = $player->getPlayerInfo()->getExtraData();
        $this->plugin->getDeviceOS()->setDeviceOS($player->getName(), $clientData['DeviceOS'] ?? null);
    }
}