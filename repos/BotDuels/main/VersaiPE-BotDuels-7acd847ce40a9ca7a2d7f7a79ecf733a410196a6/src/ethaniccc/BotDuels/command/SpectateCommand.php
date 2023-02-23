<?php

namespace ethaniccc\BotDuels\command;

use ethaniccc\BotDuels\BotDuels;
use ethaniccc\BotDuels\game\DuelGame;
use ethaniccc\BotDuels\game\GameManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\types\GameMode;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class SpectateCommand extends Command implements PluginIdentifiableCommand {

	public function __construct() {
		parent::__construct("botspec", "Spectate a running bot duel match", "/botspec <target>", []);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if ($sender instanceof Player) {
			if (GameManager::getInstance()->isInGame($sender->getName())) {
				$sender->sendMessage(TextFormat::RED . "You can't spectate bot duels while in a bot duel");
				return;
			}
			$target = $args[0] ?? null;
			if ($target === null) {
				$sender->sendMessage(TextFormat::RED . "You need to specify a player to spectate");
				return;
			}
			$targetPlayer = Server::getInstance()->getPlayer($target);
			if ($targetPlayer === null) {
				$sender->sendMessage(TextFormat::RED . "Error: Player not found");
				return;
			}
			$game = GameManager::getInstance()->getGame($targetPlayer->getName());
			if ($game === null) {
				$sender->sendMessage(TextFormat::RED . "Error: Game for {$targetPlayer->getName()} not found");
				return;
			}
			if ($game->status !== DuelGame::STATUS_RUNNING) {
				$sender->sendMessage(TextFormat::RED . "Error: Game of {$targetPlayer->getName()} is in an un-viewable state ({$game->status}), please try running this command again");
				return;
			}
			$sender->setGamemode(GameMode::SURVIVAL_VIEWER);
			$sender->teleport(Position::fromObject($game->mapData->playerSpawnPosition, $game->level));
		}
	}

	public function getPlugin(): Plugin {
		return BotDuels::getInstance();
	}

}