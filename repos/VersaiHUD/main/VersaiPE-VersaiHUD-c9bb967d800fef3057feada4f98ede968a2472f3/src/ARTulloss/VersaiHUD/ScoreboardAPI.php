<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/17/2020
 * Time: 6:04 PM
 */
declare(strict_types=1);

namespace ARTulloss\VersaiHUD;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;

class ScoreboardAPI{
    /** @var Main */
    private $main;
    /** @var array $scoreboards */
    private $scoreboards = [];
    /**
     * ScoreboardAPI constructor.
     * @param Main $main
     */
    public function __construct(Main $main) {
        $this->main = $main;
    }
    /**
     * @param Player $player
     * @param string $objectiveName
     * @param string $displayName
     */
    public function new(Player $player, string $objectiveName, string $displayName): void{
        if (isset($this->scoreboards[$player->getName()]))
            $this->remove($player);
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = "sidebar";
        $pk->objectiveName = $objectiveName;
        $pk->displayName = $displayName;
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;
        $player->sendDataPacket($pk);
        $this->scoreboards[$player->getName()] = $objectiveName;
    }
    /**
     * @param Player $player
     */
    public function remove(Player $player): void{

        if (!isset($this->scoreboards[$player->getName()]))
            return;

        $objectiveName = $this->getObjectiveName($player);
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = $objectiveName;
        $player->sendDataPacket($pk);
        unset($this->scoreboards[$player->getName()]);
    }
    /**
     * @param Player $player
     * @return null|string
     */
    public function getObjectiveName(Player $player): ?string{
        return isset($this->scoreboards[$player->getName()]) ? $this->scoreboards[$player->getName()] : \null;
    }
    /**
     * @return array
     */
    public function getScoreboards(): array{
        return $this->scoreboards;
    }
    /**
     * @param Player $player
     * @param int $score
     * @param string $message
     */
    public function setLine(Player $player, int $score, string $message): void{
        if (!isset($this->scoreboards[$player->getName()])) {
            $this->main->getLogger()->error("Cannot set a score to a player with no scoreboard");
            return;
        }
        if ($score > 15 || $score < 1) {
            $this->main->getLogger()->error("Score must be between the value of 1-15. $score out of range");
            return;
        }
        $objectiveName = $this->getObjectiveName($player);
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $objectiveName;
        $entry->type = $entry::TYPE_FAKE_PLAYER;
        $entry->customName = $message;
        $entry->score = $score;
        $entry->scoreboardId = $score;
        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_CHANGE;
        $pk->entries[] = $entry;
        $player->sendDataPacket($pk);
    }
}