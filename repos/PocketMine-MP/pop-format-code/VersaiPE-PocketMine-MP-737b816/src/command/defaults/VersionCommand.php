<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\VersionInfo;
use function count;
use function function_exists;
use function implode;
use function opcache_get_status;
use function sprintf;
use function stripos;
use function strtolower;
use const PHP_VERSION;

class VersionCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_DESCRIPTION,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_VERSION_USAGE,
			["ver", "about"]
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_VERSION);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_version_serverSoftwareName(
				TextFormat::GREEN,
				VersionInfo::NAME
			));
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_version_serverSoftwareVersion(
				TextFormat::GREEN,
				VersionInfo::getVersionObj()->getFullVersion(),
				VersionInfo::getGitHash()
			));
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_version_minecraftVersion(
				TextFormat::GREEN,
				ProtocolInfo::MINECRAFT_VERSION_NETWORK,
				(string) ProtocolInfo::CURRENT_PROTOCOL
			));
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_version_phpVersion(TextFormat::GREEN, PHP_VERSION));

			$jitColor = TextFormat::GREEN;
			if(
				function_exists('opcache_get_status') &&
				($opcacheStatus = opcache_get_status(false)) !== false &&
				isset($opcacheStatus["jit"]["on"])
			){
				$jit = $opcacheStatus["jit"];
				if($jit["on"] === true){
					$jitStatus = KnownTranslationFactory::pocketmine_command_version_phpJitEnabled(
						sprintf("CRTO: %s%s%s%s", $jit["opt_flags"] >> 2, $jit["opt_flags"] & 0x03, $jit["kind"], $jit["opt_level"])
					);
					$jitColor = TextFormat::YELLOW;
				}else{
					$jitStatus = KnownTranslationFactory::pocketmine_command_version_phpJitDisabled();
				}
			}else{
				$jitStatus = KnownTranslationFactory::pocketmine_command_version_phpJitNotSupported();
			}
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_version_phpJitStatus($jitColor, $sender->getLanguage()->translate($jitStatus)));
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_version_operatingSystem(TextFormat::GREEN, Utils::getOS()));
		}else{
			$pluginName = implode(" ", $args);
			$exactPlugin = $sender->getServer()->getPluginManager()->getPlugin($pluginName);

			if($exactPlugin instanceof Plugin){
				$this->describeToSender($exactPlugin, $sender);

				return true;
			}

			$found = false;
			$pluginName = strtolower($pluginName);
			foreach($sender->getServer()->getPluginManager()->getPlugins() as $plugin){
				if(stripos($plugin->getName(), $pluginName) !== false){
					$this->describeToSender($plugin, $sender);
					$found = true;
				}
			}

			if(!$found){
				$sender->sendMessage(KnownTranslationFactory::pocketmine_command_version_noSuchPlugin());
			}
		}

		return true;
	}

	private function describeToSender(Plugin $plugin, CommandSender $sender) : void{
		$desc = $plugin->getDescription();
		$sender->sendMessage(TextFormat::DARK_GREEN . $desc->getName() . TextFormat::WHITE . " version " . TextFormat::DARK_GREEN . $desc->getVersion());

		if($desc->getDescription() !== ""){
			$sender->sendMessage($desc->getDescription());
		}

		if($desc->getWebsite() !== ""){
			$sender->sendMessage("Website: " . $desc->getWebsite());
		}

		if(count($authors = $desc->getAuthors()) > 0){
			if(count($authors) === 1){
				$sender->sendMessage("Author: " . implode(", ", $authors));
			}else{
				$sender->sendMessage("Authors: " . implode(", ", $authors));
			}
		}
	}
}
