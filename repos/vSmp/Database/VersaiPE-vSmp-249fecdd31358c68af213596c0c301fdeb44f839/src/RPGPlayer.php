<?php

declare(strict_types = 1);

/**
 * This file extends pocketmine\player\Player to shorten, or add more features to the default player.
 * 
 * @author Versai
 * @version 0.0.1
 */

namespace Versai\RPGCore;

use pocketmine\player\Player;
use pocketmine\entity\Attribute;

class RPGPlayer extends Player {

	/** @var int **/
	public int $level = 1;
	
	# Mana Management #
	
	/**
	* Sets player's mana with specified integer
	*
	* @param float $mana
	**/
	public function setMana(float $mana) : void {
		$this->getHungerManager()->setFood($mana);
	}
	
	/**
	* Reduces player's mana with specified integer
	*
	* @param float $mana
	**/
	public function reduceMana(float $mana) : void {
		$this->getHungerManager()->setFood($this->getHungerManager()->getFood() - $mana);
	}
	
	/**
	* Resets player's mana to least mana possible (0)
	**/
	public function resetMana() : void {
		$this->getHungerManager()->setFood(0);
	}
	
	/**
	* Resets player's mana to max mana possible (20)
	**/
	public function chargeMana() : void {
		$this->getHungerManager()->setFood(20);
	}
	
	/**
	* Gets a player's mana
	*
	* @return float
	**/
	public function getMana() : float {
		return $this->getHungerManager()->getFood();
	}
	
	# Level Management #
	
	/**
	* Gets player's XP level (in-game level)
	*
	* @return int
	**/
	public function getLevel() : int {
		return $this->getXpManager()->getXpLevel();
	}
	
	/**
	* Sets player's XP level with specified integer(in-game level)
	**/
	public function setLevel(int $level) : void {
		$this->getXpManager()->setXpLevel($level);
	}
	
	# Stats Management #
	
	/**
	* Gets player's movement speed value
	*
	* @return int
	**/
	public function getAgility() : mixed {
		$movement = $this->getAttributeMap()->get(Attribute::MOVEMENT_SPEED);
		return $movement->getValue();
	}
	
	/**
	* Sets player's movement speed value
	*
	* Max = 00.25
	*
	* @param float $value
	**/
	public function setAgility(float $value) : void {
        $movement = $this->getAttributeMap()->get(Attribute::MOVEMENT_SPEED);
        $movement->setValue($value);
    }
	
	/**
	* Gets player's max HP
	*
	* @return int
	**/
	public function getVitality() : int {
		return $this->getMaxHealth();
	}
	
	/**
	* Sets player's max HP
	*
	* @param int $value
	**/
	public function setVitality(int $value) : void {
		$this->setMaxHealth($value);
		$this->setHealth($this->getMaxHealth());
	}
	
	# World Management #
	
	/**
	* Gets player's current world name
	*
	* @return string
	**/
	public function getWorldName() : string {
		return $this->getWorld()->getFolderName();
	}
}