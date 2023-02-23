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
    private int $miningLevel = 0;
    private int $miningXP = 0;
    private int $woodcuttingLevel = 0;
    private int $woodcuttingXP = 0;
    private int $farmingLevel = 0;
    private int $farmingXP = 0;
    private int $fishingLevel = 0;
    private int $fishingXP = 0;
    private int $combatLevel = 0;
    private int $combatXP = 0;

    private Player $player;

    public function __construct(Player $player){
        $this->player = $player;
    }

    public function getPlayer(): Player{
        return $this->player;
    }

    public function getQuest() {
        return $this->quest;
    }

    public function nextQuest() {
        $this->questId += 1;
    }

    public function getQuestId() {
        return $this->questId;
    }

    public function setQuestId(int $id) {
        $this->questId = $id;
    }

    public function getQuestProgress() {
        return $this->questProgress;
    }

    public function setQuestProgress(int $progress) {
        $this->questProgress = $progress;
    }

    public function setQuest(Quest $quest) {
        $this->quest = $quest;
    }

    public function getQuestRequired() {
        return $this->quest->getRequirement();
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

    public function hasCoins(int $coins): bool {
        if ($this->coins >= $coins) {
            return true;
        } else return false;
    }

    public function getMiningLevel() {
        return $this->miningLevel;
    }

    public function setMiningLevel(int $level) {
        $this->miningLevel = $level;
    }

    public function getMiningXP() {
        return $this->miningXP;
    }

    public function setMiningXP(int $xp) {
        $this->miningXP = $xp;
    }

    public function addMiningXP(int $xp) {
        $this->miningXP += $xp;
    }

    public function removeMiningXP(int $xp) {
        $this->miningXP -= $xp;
    }

    public function getWoodcuttingLevel() {
        return $this->woodcuttingLevel;
    }

    public function setWoodcuttingLevel(int $level) {
        $this->woodcuttingLevel = $level;
    }

    public function getWoodcuttingXP() {
        return $this->woodcuttingXP;
    }

    public function setWoodcuttingXP(int $xp) {
        $this->woodcuttingXP = $xp;
    }

    public function addWoodcuttingXP(int $xp) {
        $this->woodcuttingXP += $xp;
    }

    public function removeWoodcuttingXP(int $xp) {
        $this->woodcuttingXP -= $xp;
    }

    public function getFishingLevel() {
        return $this->fishingLevel;
    }

    public function setFishingLevel(int $level) {
        $this->fishingLevel = $level;
    }

    public function getFishingXP() {
        return $this->fishingXP;
    }

    public function setFishingXP(int $xp) {
        $this->fishingXP = $xp;
    }

    public function addFishingXP(int $xp) {
        $this->fishingXP += $xp;
    }

    public function removeFishingXP(int $xp) {
        $this->fishingXP -= $xp;
    }

    public function getFarmingLevel() {
        return $this->farmingLevel;
    }

    public function setFarmingLevel(int $level) {
        $this->farmingLevel = $level;
    }

    public function getFarmingXP() {
        return $this->farmingXP;
    }

    public function setFarmingXP(int $xp) {
        $this->farmingXP = $xp;
    }

    public function addFarmingXP(int $xp) {
        $this->farmingXP += $xp;
    }

    public function removeFarmingXP(int $xp) {
        $this->farmingXP -= $xp;
    }

    public function getCombatLevel() {
        return $this->combatLevel;
    }

    public function setCombatLevel(int $level) {
        $this->combatLevel = $level;
    }

    public function getCombatXP() {
        return $this->combatXP;
    }

    public function setCombatXP(int $xp) {
        $this->combatXP = $xp;
    }

    public function addCombatXP(int $xp) {
        $this->combatXP += $xp;
    }

    public function removeCombatXP(int $xp) {
        $this->combatXP -= $xp;
    }
}