<?php
declare(strict_types=1);

namespace Duo\vcosmetics\events\settings\listeners;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\world\World;
use Duo\vcosmetics\constants\Permissions;
use Duo\vcosmetics\events\settings\FlightSetEvent;
use Duo\vcosmetics\Main;

class FlightListener implements Listener {

	private Main $plugin;

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
	}

	public function onJoin(PlayerJoinEvent $event): void{

		$player = $event->getPlayer();
		$this->setFlight($player, $player->getWorld(), false);

	}

	public function setFlight(Player $player, World $level, bool $state): void{

		if ($player->getGamemode() === GameMode::CREATIVE() || $player->getGamemode() === GameMode::SPECTATOR()) {
            return;
        }

		if ($level === $this->plugin->getServer()->getWorldManager()->getDefaultWorld() && $player->hasPermission(Permissions::FLIGHT)) {
            $player->setAllowFlight($state);
        } else {
			$player->setAllowFlight(false);
			$player->setFlying(false);
		}
	}

	public function onWorldChange(EntityTeleportEvent $event): void {
		$player = $event->getEntity();
		$from = $event->getFrom();
		$to = $event->getTo();

		if($from->getWorld()->getFolderName() !== $to->getWorld()->getFolderName()) {
            if ($player instanceof Player) {
                $this->setFlight($player, $event->getTo()->getWorld(), false);
            }
        }
	}

	public function onToggle(FlightSetEvent $event) {
		$player = $event->getPlayer();
		$this->setFlight($player, $player->getWorld(), $event->getState());
	}

}