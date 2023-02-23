<?php

declare(strict_types=1);

namespace Versai\RPGCore\Utils;

use Versai\RPGCore\Main;
use Versai\RPGCore\RPGPlayer;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class ScoreboardManager {

	private const OBJECTIVE_NAME = "objective";
	private const CRITERIA_NAME = "dummy";

	use SingletonTrait;
	private Main $plugin;
    
    public function __construct(Main $plugin) {
		$this->plugin = $plugin;
        self::setInstance($this);
	}
	
    public function setScore(
        Player $player,
        string $displayName,
        int $slotOrder,
        string $displaySlot,
        string $objectiveName = self::OBJECTIVE_NAME,
        string $criteriaName = self::CRITERIA_NAME
    ): void {
        if (!$player->isConnected()) {
            return;
        }

        $pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = $displaySlot;
		$pk->objectiveName = $objectiveName;
		$pk->displayName = $displayName;
		$pk->criteriaName = $criteriaName;
		$pk->sortOrder = $slotOrder;
		$player->getNetworkSession()->sendDataPacket($pk);
    }

    public function removeScore(Player $player): void {
        if (!$player->isConnected()){
            return;
        }
		$objectiveName = self::OBJECTIVE_NAME;

		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = $objectiveName;
		$player->getNetworkSession()->sendDataPacket($pk);
	}

    public function setScoreLine(Player $player, int $line, string $message, int $type = ScorePacketEntry::TYPE_FAKE_PLAYER): void {
        if (!$player->isConnected()) {
            return;
        }
		$entry = new ScorePacketEntry();
		$entry->objectiveName = self::OBJECTIVE_NAME;
		$entry->type = $type;
		$entry->customName = $message;
		$entry->score = $line;
		$entry->scoreboardId = $line;
		$pk = new SetScorePacket();
		$pk->type = $pk::TYPE_CHANGE;
		$pk->entries[] = $entry;
		$player->getNetworkSession()->sendDataPacket($pk);
	}
	
	public function update() {
		foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
			$this->removeScore($player);
            $this->setScore($player, "§l§aVersai §2SMP", SetDisplayObjectivePacket::SORT_ORDER_ASCENDING, SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR); # Scoreboard Title
            # LINES
            $this->setScoreLine($player, 0, "§6Coins: §70");
			$this->setScoreLine($player, 1, "§9Defense: §70");
			$this->setScoreLine($player, 2, "§aLevel: §70");
		}
	}
}	