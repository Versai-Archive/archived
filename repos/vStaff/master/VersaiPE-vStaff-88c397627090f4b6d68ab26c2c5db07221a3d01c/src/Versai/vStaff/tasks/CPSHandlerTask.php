<?php

namespace Versai\vStaff\tasks;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use Versai\vStaff\Main;

class CPSHandlerTask extends Task {

	private Main $plugin;
	private Player $player;
	private string $victim;

	public function __construct(Main $plugin, Player $player, string $victim) {
		$this->plugin = $plugin;
		$this->player = $player;
		$this->victim = $victim;
	}

	public function onRun(): void {
		if($this->victim !== null) {
			$clicks = $this->plugin->cps[$this->victim];
			$clicks = floor($clicks / 5);
			$this->player->sendMessage(TextFormat::GREEN . $this->victim . " clicked " . $clicks . " cps on average in the last 5 seconds.");
			if(($this->plugin->cps[$this->victim]) !== null) {
				unset($this->plugin->cps[$this->victim]);
			}
		}
	}
}
