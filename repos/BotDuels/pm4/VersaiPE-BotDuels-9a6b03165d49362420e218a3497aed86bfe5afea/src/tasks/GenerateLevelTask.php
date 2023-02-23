<?php

namespace ethaniccc\BotDuels\tasks;

use ethaniccc\BotDuels\game\DuelGame;
use ethaniccc\BotDuels\game\GameManager;
use ethaniccc\BotDuels\map\MapData;
use ethaniccc\BotDuels\Utils;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class GenerateLevelTask extends AsyncTask {

	private MapData $mapData;
	private string $newName;

	public function __construct(MapData $mapData, string $newName, DuelGame &$game) {
		$this->mapData = $mapData;
		$this->newName = $newName;
		$this->storeLocal("game", $game);
	}

	public function onRun(): void {
		Utils::recurseCopy($this->mapData->path, "./worlds/{$this->newName}");
	}

	public function onCompletion(): void {
		/** @var DuelGame $game */
		$game = $this->fetchLocal("game");
		$server = Server::getInstance();
		if (!$server->getWorldManager()->loadWorld($this->newName)) {
			GameManager::getInstance()->remove($game);
			$game->player->sendMessage(TextFormat::RED . "Something went wrong when attempting to generate the duel world (worldName={$game->mapData->name})");
			$game->status = DuelGame::STATUS_END;
		} else {
			$game->level = $server->getWorldManager()->getWorldByName($this->newName);
			$game->status = DuelGame::FIRST_RUN;
		}
	}

}