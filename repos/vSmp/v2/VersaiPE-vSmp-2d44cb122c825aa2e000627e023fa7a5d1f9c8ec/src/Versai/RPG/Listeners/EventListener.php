<?php

declare(strict_types=1);

namespace Versai\RPG\Listeners;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use Versai\RPG\Main;
use Versai\RPG\RPGPlayer;

class EventListener implements Listener {

    public function playerCreation(PlayerCreationEvent $event) {
        $event->setPlayerClass(RPGPlayer::class);
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $event->setJoinMessage("§7[§a+§7] {$player->getName()}");
    }

    public function onLeave(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $event->setQuitMessage("§7[§c-§7] {$player->getName()}");
    }

}