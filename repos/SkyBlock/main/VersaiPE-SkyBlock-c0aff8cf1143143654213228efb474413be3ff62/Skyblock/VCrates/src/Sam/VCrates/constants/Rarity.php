<?php


namespace Sam\VCrates\constants;


use pocketmine\utils\TextFormat as TF;

class Rarity{
	public const COMMON = TF::GRAY . TF::BOLD . "COMMON " . TF::RESET;
	public const RARE = TF::AQUA . TF::BOLD . "RARE " . TF::RESET;
	public const EPIC = TF::LIGHT_PURPLE . TF::BOLD . "EPIC " . TF::RESET;
	public const LEGENDARY = TF::GOLD . TF::BOLD . "LEGENDARY " . TF::RESET;

}