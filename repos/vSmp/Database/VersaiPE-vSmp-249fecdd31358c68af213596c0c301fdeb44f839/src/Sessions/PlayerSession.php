<?php
declare(strict_types=1);

namespace Versai\RPGCore\Sessions;

use pocketmine\player\Player;
use Versai\RPGCore\Quests\Quest;

class PlayerSession {

    private string $class = ""; 
    private int $mana = 0; 
    private int $maxMana = 0;
    private int $defense = 0;
    private float $agility = 0.0;
    private int $coins = 0;
    /** @var Quest */
    private $quest;
    private int $questId = 0;
    private int $questProgress = 0;

    private Player $player;

    public function __construct(Player $player){
        $this->player = $player;
    }

    public function getPlayer(): Player {
        return $this->player;
    }

    public function setQuestId(int $questId) {
        $this->questId = $questId;
    }

    public function getQuestId() {
        return $this->questId;
    }

    public function nextQuest() {
        $this->questId += 1;
    }

    public function getQuestProgress() {
        return $this->questProgress; 
    }

    public function setQuestProgress(int $progress) {
        $this->questProgress = $progress;
    }

    public function getQuest() {
        return $this->quest;
    }

    public function setQuest(Quest $quest) {
        $this->quest = $quest;
    }

    public function getQuestRequired() {
        return $this->quest->getRequirment();
    }

    public function getClass() {
        return $this->class;
    }

    public function setClass(string $class) {
        $this->class = $class;
    }

    public function getMana() {
        return $this->mana;
    }

    public function setMana(int $mana) {
        $this->mana = $mana;
    }

    public function addMana(int $mana) {
        $this->mana += $mana;
    }

    public function getMaxMana() {
        return $this->maxMana;
    }

    public function setMaxMana(int $maxMana) {
        $this->maxMana = $maxMana;
    }

    public function getDefense() {
        return $this->defense;
    }

    public function setDefense(int $defense) {
        $this->defense = $defense;
    }

    public function getAgility() {
        return $this->agility;
    }

    public function setAgility(float $agility) {
        $this->agility = $agility;
    }

    public function getCoins() {
        return $this->coins;
    }

    public function setCoins(int $coins) {
        $this->coins = $coins;
    }

    public function addCoins(int $coins) {
        $this->coins += $coins;
    }

    public function removeCoins(int $coins) {
        $this->coins -= $coins;
    }
}