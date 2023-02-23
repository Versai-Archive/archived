<?php


namespace Sam\VCrates\constants;


use pocketmine\utils\TextFormat as TF;

class Messages{
	public const NO_PREFIX = TF::GRAY . "[" . TF::RED . "×" . TF::GRAY . "] ";
	public const NEGATIVE_PREFIX = TF::GRAY . "[" . TF::RED . "-" . TF::GRAY . "] ";
	public const YES_PREFIX = TF::GRAY . "[" . TF::GREEN . "OK" . TF::GRAY . "] ";
	public const POSITIVE_PREFIX = TF::GRAY . "[" . TF::GREEN . "+" . TF::GRAY . "] ";
	public const NO_PERMISSIONS = TF::GRAY . "You do not have permission to use this command.";
	public const PLAYER_ADDED_KEY = TF::GRAY . "Added the keys successfully";
	public const PLAYER_REMOVED_KEY = TF::GRAY . "Removed the keys successfully";
	public const PLACE_CRATE_ON = TF::GRAY . "Placing crates mode enabled. Right click a chest.";
	public const PLACE_CRATE_OFF = TF::GRAY . "Placing crates mode disabled";
	public const ALREADY_IN_USE = TF::GRAY . "The crate is already in use.";
	public const NO_KEY = TF::GRAY . "You do not have any keys!";
	public const WON_LEGENDARY = Rarity::LEGENDARY . TF::AQUA;
	public const PLACED = TF::GRAY . "Placed crate successfully";

}