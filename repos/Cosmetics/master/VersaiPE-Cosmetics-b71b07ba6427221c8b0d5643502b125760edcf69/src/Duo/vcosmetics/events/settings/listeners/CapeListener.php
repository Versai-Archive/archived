<?php
declare(strict_types=1);

namespace Duo\vcosmetics\events\settings\listeners;

use pocketmine\entity\Skin;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use Duo\vcosmetics\events\settings\CapeSetEvent;
use Duo\vcosmetics\Main;
use function array_values;

class CapeListener implements Listener {

	private Main $plugin;

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * @param Player $player
	 * @param string $capeData
	 */
	public function givePlayerCape(Player $player, string $capeData): void{
		$oldSkin = $player->getSkin();
		$skin = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
		$player->setSkin($skin);
		$player->sendSkin();
	}

	/**
	 * @param CapeSetEvent $event
     * @priority HIGHEST
	 */
	public function onToggle(CapeSetEvent $event): void{
		$this->givePlayerCape($event->getPlayer(), array_values($this->plugin->capes)[$event->getState()]);
	}

}