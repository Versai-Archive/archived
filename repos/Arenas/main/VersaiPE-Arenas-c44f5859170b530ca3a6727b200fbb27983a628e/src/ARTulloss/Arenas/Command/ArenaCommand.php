<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 12/13/2018
 * Time: 3:20 PM
 */

declare(strict_types=1);

namespace ARTulloss\Arenas\Command;

use ARTulloss\Arenas\Arena;
use ARTulloss\Arenas\Arenas;
use ARTulloss\Arenas\Constants;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class ArenaCommand extends PluginCommand
{


	/**
	 * ArenaCommand constructor.
	 * @param $name
	 * @param Plugin $owner
	 */
	public function __construct($name, Plugin $owner)
	{
		parent::__construct($name, $owner);
		$this->setDescription(Constants::DESCRIPTION);
		$this->setUsage("/arenas <create> <remove> <info> <list> <set>");
		$this->setPermission(Constants::PERMISSION);
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void
	{

		if (!$sender->hasPermission(Constants::PERMISSION)) {
			$sender->sendMessage(TextFormat::BLUE . "Arenas v1 by ARTulloss");
			return;
		}

		$this->setUsage("/arenas <create> <remove> <info> <list> <set>");

		if (\count($args) === 0)
			throw new InvalidCommandSyntaxException();

		$server = $this->getPlugin();

		$this->setUsage("/arenas <create> <remove> <info> <list>");

		if (!$server instanceof Arenas)
			return;

		switch ($args[0]) {

			case "create":

				if ($sender instanceof Player) {

					$this->setUsage("/arenas create <protection> <knockback> <cooldown> <fall-damage> <lightning>");

					$levelName = $sender->getLevel()->getName();

					if (isset($args[1])) {

						if ($args[1] === "help" || !\is_numeric($args[1]))
							throw new InvalidCommandSyntaxException();

						if (isset($args[2]) && !\is_numeric($args[2]))
							throw new InvalidCommandSyntaxException();

					}

					if (isset($server->arenas[$levelName])) {
						$sender->sendMessage(Constants::ARENA_EXISTS);
						return;
					}

					$default = $server->getConfig()->getAll()["Defaults"];

					// public function __construct(string $name, Vector3 $location, int $protection, float $knockback, int $hitdelay, bool $lightning){

					$arena = new Arena($levelName, (array)[0], $sender->getLocation(), [isset($args[1]) ? (int)$args[1] : (int)$default["Protection"],
						isset($args[2]) ? (float)$args[2] : (float)$default["Knockback"], isset($args[3]) ? (int)$args[3] : (int)$default["Cooldown"],
						isset($args[4]) ? $this->toBool($args[4]) : (bool)$default["Fall-Damage"], isset($args[5]) ? $this->toBool($args[5]) : (bool)$default["Lightning"]]);

					$server->saveArena($arena);
					$sender->sendMessage(\str_replace("{arena}", $levelName, Constants::CREATED_ARENA));
					$server->loadArenas();

				} else
					$sender->sendMessage(Constants::PLAYER_ONLY);

				break;

			case "remove":

				$this->setUsage("/arenas delete <arena>");

				if (isset($args[1])) {

					if (\count($args) !== 2) {
						$sender->sendMessage(Constants::INVALID_ARGUMENT_NUMBER);
						throw new InvalidCommandSyntaxException();
					}

					if (\file_exists($server->path . $args[1] . ".json")) {
						\unlink($server->path . $args[1] . ".json");
						unset($server->arenas[$args[1]]);
						$sender->sendMessage(\str_replace("{arena}", $args[1], Constants::DELETED_ARENA));
						$server->loadArenas();
					} else
						$sender->sendMessage(Constants::ARENA_NOT_EXIST);

				} else {
					$sender->sendMessage(Constants::SET_ARENA);
					throw new InvalidCommandSyntaxException();
				}
				break;

			case "list":

				$this->setUsage("/arenas list <arena>");

				if (isset($args[2])) {
					$sender->sendMessage(Constants::INVALID_ARGUMENT_NUMBER);
					throw new InvalidCommandSyntaxException();
				}

				$sender->sendMessage("The arenas are:");

				foreach ($server->arenas as $key => $arena) {
					$sender->sendMessage($key);
				}

				break;

			case "info":

				$this->setUsage("/arenas info | info <arena>");

				if (isset($args[1])) {

					if (isset($args[2])) {
						$sender->sendMessage(Constants::INVALID_ARGUMENT_NUMBER);
						throw new InvalidCommandSyntaxException();
					}

					if (isset($server->arenas[$args[1]]))
						$this->sendInfo($server, $sender, $args[1]);
					else
						$sender->sendMessage(Constants::ARENA_NOT_EXIST);

				} else
					if ($sender instanceof Player && ($levelName = $sender->getLevel()->getName()) && isset($levelName) && isset($server->arenas[$levelName]))
						$this->sendInfo($server, $sender, $levelName);
					else {
						$this->setUsage("/arenas info <arena>");
						throw new InvalidCommandSyntaxException();
					}

				break;

			case "set":

				$this->setUsage("/arenas set <knockback> | <protection> | <cooldown> && <arena> && <value>");

				if (isset($args[1])) {

					// if(($f = $server->arenas[$levelName]) && isset($f) && $f instanceof Arena)

					switch ($args[1]) {

						case "kb":
						case "knockback":

							if (isset($args[2]) && isset($args[3]))
								if (isset($server->arenas[$args[2]]))
									$server->arenas[$args[2]]->setKnockback((float)$args[3]);
								else {
									$sender->sendMessage(Constants::ARENA_NOT_EXIST);
									return;
								}
							else
								throw new InvalidCommandSyntaxException();

							$sender->sendMessage(\str_replace(["{arena}", "{value}"], [$args[2], $args[3]], Constants::SET_KNOCKBACK));

							break;

						case "protection":

							if (isset($args[2]) && isset($args[3]))
								if (isset($server->arenas[$args[2]]))
									$server->arenas[$args[2]]->setProtection((int)$args[3]);
								else {
									$sender->sendMessage(Constants::ARENA_NOT_EXIST);
									return;
								}
							else
								throw new InvalidCommandSyntaxException();

							$sender->sendMessage(\str_replace(["{arena}", "{value}"], [$args[2], $args[3]], Constants::SET_PROTECTION));

							break;

						case "cooldown":

							if (isset($args[2]) && isset($args[3]))
								if (isset($server->arenas[$args[2]]))
									$server->arenas[$args[2]]->setCooldown((int)$args[3]);
								else {
									$sender->sendMessage(Constants::ARENA_NOT_EXIST);
									return;
								}
							else
								throw new InvalidCommandSyntaxException();

							$sender->sendMessage(\str_replace(["{arena}", "{value}"], [$args[2], $args[3]], Constants::SET_COOLDOWN));

							break;

						case "fall" || "fall_damage":

							if (isset($args[2]) && isset($args[3]))
								if (isset($server->arenas[$args[2]]))
									$server->arenas[$args[2]]->setFallDamage($this->toBool($args[3]));
								else {
									$sender->sendMessage(Constants::ARENA_NOT_EXIST);
									return;
								} else
								throw new InvalidCommandSyntaxException();

							$sender->sendMessage(\str_replace(["{arena}", "{value}"], [$args[2], $args[3]], Constants::SET_FALL_DAMAGE));

							break;

						case "lightning":

							if (isset($args[2]) && isset($args[3]))
								if (isset($server->arenas[$args[2]]))
									$server->arenas[$args[2]]->setLightning($this->toBool($args[3]));
								else {
									$sender->sendMessage(Constants::ARENA_NOT_EXIST);
									return;
								} else
								throw new InvalidCommandSyntaxException();

							$sender->sendMessage(\str_replace(["{arena}", "{value}"], [$args[2], $args[3]], Constants::SET_COOLDOWN));

							break;

						case "spawn":

							$this->setUsage("/arenas set spawn");

							if ($sender instanceof Player) {

								if (($levelName = $sender->getLevel()->getName()) && isset($levelName) && isset($server->arenas[$levelName])) {
									$pos = $sender->getPosition()->asVector3();
									$server->arenas[$levelName]->setLocation($pos);
									$sender->sendMessage(\str_replace(["{arena}", "{x}", "{y}", "{z}"], [$levelName, $pos->getX(), $pos->getY(), $pos->getZ()], Constants::SET_POSITION));
								} else
									throw new InvalidCommandSyntaxException();
							} else {
								$sender->sendMessage(Constants::PLAYER_ONLY);
								return;
							}
							break;

						default:
							throw new InvalidCommandSyntaxException();
					}
					if (isset($args[2]))
						$arena = $args[2];
					elseif ($sender instanceof Player)
						$arena = $sender->getLevel()->getName();
					else
						throw new \Error("Undefined level!");
					$server->saveArena($server->arenas[$arena]);
				} else
					throw new InvalidCommandSyntaxException();

				break;

			default:

				$this->setUsage("/arenas <create> <remove> <info> <list> <set>");

				throw new InvalidCommandSyntaxException();
		}

	}

	/**
	 * @param string $var
	 * @return bool
	 */
	public function toBool(string $var): bool
	{
		switch (\strtolower($var)) {
			case "1":
			case "true":
			case "on":
			case "yes":
			case "y":
				return \true;
			default:
				return \false;
		}
	}

	public function sendInfo(Arenas $server, CommandSender $sender, string $arenaName)
	{

		$sender->sendMessage(TextFormat::BLUE . "Info for arena: " . $arenaName);

		$sender->sendMessage(TextFormat::BLUE . "Protection: " . $server->arenas[$arenaName]->getProtection());
		$sender->sendMessage(TextFormat::BLUE . "Knockback: " . $server->arenas[$arenaName]->getKnockback());
		$sender->sendMessage(TextFormat::BLUE . "Cooldown: " . $server->arenas[$arenaName]->getHitCooldown());
		$sender->sendMessage(TextFormat::BLUE . "Fall Damage: " . $this->boolToString($server->arenas[$arenaName]->hasFallDamage()));
		$sender->sendMessage(TextFormat::BLUE . "Lightning: " . $this->boolToString($server->arenas[$arenaName]->hasLightning()));

	}

	/**
	 * @param bool $bool
	 * @return string
	 */
	public function boolToString(bool $bool): string
	{

		return $bool ? "true" : "false";

	}
}