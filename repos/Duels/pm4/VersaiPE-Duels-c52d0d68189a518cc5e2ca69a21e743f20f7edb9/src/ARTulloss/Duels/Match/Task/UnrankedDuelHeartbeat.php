<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/23/2019
 * Time: 6:34 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Match\Task;

use pocketmine\player\Player;
use function array_keys;
use function array_values;
use ARTulloss\Duels\Utilities\Utilities;
use pocketmine\utils\TextFormat;
use function str_replace;

/**
 * Class UnrankedDuelHeartbeat
 * @package ARTulloss\Duels\Match\Task
 */
class UnrankedDuelHeartbeat extends Heartbeat
{
	protected const TYPE = 'Unranked';
	protected const DUEL_LENGTH_KEY = '1v1-Duel-Length';
	protected const END_MESSAGE = TextFormat::GRAY . '{winner} won an unranked match against {loser} with type {type}!';

	public function sendPlayersScoreboard(): void
	{
		/** @var Player[] $players */
		$players = array_values($this->players);

		$spectatorCount = count((array)$this->getSpectators());
		if(isset($players[0]) && isset($players[1])) {
			$this->scoreboardTask->setTextForByName($players[0]->getName(), str_replace(['{ping}', '{oping}', '{ranked}', '{kit}', '{combat}', '{time}', '{spectators}'], [$players[0]->getPing(), $players[1]->getPing(), static::TYPE, $this->kitType, $this->duels->cooldown->combat->getCooldown($players[0]), $this->countdown, $spectatorCount], $this->duels->scoreboardArray['1v1']));
			$this->scoreboardTask->setTextForByName($players[1]->getName(), str_replace(['{ping}', '{oping}', '{ranked}', '{kit}', '{combat}', '{time}', '{spectators}'], [$players[1]->getPing(), $players[0]->getPing(), static::TYPE, $this->kitType, $this->duels->cooldown->combat->getCooldown($players[1]), $this->countdown, $spectatorCount], $this->duels->scoreboardArray['1v1']));
		}
	}

	public function sendEndMatchScoreboard(): void
	{
		foreach ($this->players as $player)
			$this->scoreboardTask->setTextForByName($player->getName(), str_replace(['{ranked}', '{kit}', '{opponent}', '{spectators}'], [static::TYPE, $this->kitType, array_values($this->removedPlayers)[0]->getDisplayName(), count((array)$this->getSpectators())], $this->duels->scoreboardArray['1v1-Win']));
	}

	public function sendEndMatchBossbar(): void
	{
		foreach ($this->players as $player) {
			$this->bossbarTask->setTextForByName($player->getName(), (array) str_replace(['{time}', '{rtime}'], [$this->countdown, Utilities::secondsToReadableTime($this->countdown)], $this->duels->bossbarArray['1v1-Win']), true);
			$this->bossbarTask->setHealthProgressByName($player->getName(), $this->matchEndTimer, $this->duels->duelConfig['Settings']['End-Game-Time']);
		}
	}

	public function sendSpectatorsScoreboard(): void
	{
		$ak = array_keys($this->players);
		if(isset($ak[0]))
		    $player1Name = $ak[0];
		else
		    return;
		if(isset($ak[1]))
			$player2Name = $ak[1];
		else {
		    $ak = array_keys($this->removedPlayers);
		    if(!isset($ak[0]))
		        $player2Name = $ak[0];
		    else
		        return;
		} foreach ($this->spectatingPlayers as $player)
			$this->scoreboardTask->setTextForByName($player->getName(), str_replace(['{time}', '{rtime}', '{spectators}', '{ranked}', '{kit}', '{player1}', '{player2}'], [$this->countdown, Utilities::secondsToReadableTime($this->countdown), count($this->spectatingPlayers), static::TYPE, $this->kitType, $player1Name, $player2Name], $this->duels->scoreboardArray['1v1-Spectate']));
	}

	public function sendPlayersBossbar(): void
	{
		foreach ($this->players as $player) {
			$this->bossbarTask->setTextForByName($player->getName(), (array) str_replace(['{time}', '{rtime}'], [$this->countdown, Utilities::secondsToReadableTime($this->countdown)], $this->duels->bossbarArray['1v1']), true);
			$this->bossbarTask->setHealthProgressByName($player->getName(), $this->countdown, $this->duels->duelConfig['Settings'][static::DUEL_LENGTH_KEY]);
		}
	}

	public function sendSpectatorsBossbar(): void
	{
		foreach (array_keys($this->spectatingPlayers) as $name) {
			$this->bossbarTask->setTextForByName($name, (array) str_replace(['{time}', '{rtime}'], [$this->countdown, Utilities::secondsToReadableTime($this->countdown)], $this->duels->bossbarArray['1v1-Spectate']), true);
			$this->bossbarTask->setHealthProgressByName($name, $this->countdown, $this->duels->duelConfig['Settings'][static::DUEL_LENGTH_KEY]);
		}
	}

    /**
     * @return null|Player
     */
	public function getLoser(): ?Player{
        $ak = array_keys($this->removedPlayers);

        if(!isset($ak[0])) // Otherwise crash if player leaves
            return null;

        if($this->stage !== Heartbeat::STAGE_FINISHED)
            return null;

        return array_values($this->removedPlayers)[0];
    }

}