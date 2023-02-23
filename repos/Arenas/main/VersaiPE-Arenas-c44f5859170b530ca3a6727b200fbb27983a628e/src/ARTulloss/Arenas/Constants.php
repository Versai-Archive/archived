<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 12/13/2018
 * Time: 3:23 PM
 */

declare(strict_types=1);

namespace ARTulloss\Arenas;

use pocketmine\utils\TextFormat;

class Constants
{
	// Command

	public const PREFIX = TextFormat::BLUE . "[Arenas] ";

	public const DESCRIPTION = "Manage arenas!";

	public const PERMISSION = "arenas.command";

	public const PLAYER_ONLY = self::PREFIX . "You must be a player to use this command!";

	public const INVALID_ARGUMENT_NUMBER = self::PREFIX . "Invalid number of arguments!";

	public const SET_ARENA = self::PREFIX . "You have to enter the arena's name!";

	public const ARENA_EXISTS = self::PREFIX . "This arena already exists, please delete the arena and try again!";
	public const ARENA_NOT_EXIST = self::PREFIX . "Arena doesn't exist!";

	public const CREATED_ARENA = self::PREFIX . "Successfully created arena {arena}! do /arenas info to double check settings.";
	public const DELETED_ARENA = self::PREFIX . "Successfully deleted arena {arena}!";

	public const SET_KNOCKBACK = self::PREFIX . "Knockback on arena {arena} set to {value}";
	public const SET_COOLDOWN = self::PREFIX . "Hit cooldown on arena {arena} set to {value}";
	public const SET_PROTECTION = self::PREFIX . "Protection on arena {arena} set to {value} blocks";
	public const SET_POSITION = self::PREFIX . "Protection on arena {arena} set to {x} {y} {z}";
	public const SET_FALL_DAMAGE = self::PREFIX . "Fall damage set to {value}";
	public const SET_LIGHTNING = self::PREFIX . "Lightning set to {value}";

}