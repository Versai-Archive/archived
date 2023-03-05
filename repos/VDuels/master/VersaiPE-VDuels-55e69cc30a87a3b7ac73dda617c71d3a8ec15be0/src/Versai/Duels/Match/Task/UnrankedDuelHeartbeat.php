<?php
declare(strict_types=1);

namespace Versai\Duels\Match\Task;

use Ifera\ScoreHud\libs\JackMD\ScoreFactory\ScoreFactory;
use Ifera\ScoreHud\scoreboard\Scoreboard;
use Ifera\ScoreHud\session\PlayerManager;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\Duels\Utilities\Utilities;
use function array_keys;
use function array_values;
use function str_replace;

class UnrankedDuelHeartbeat extends Heartbeat {

	protected const TYPE = 'Unranked';
	protected const DUEL_LENGTH_KEY = '1v1-Duel-Length';
	protected const END_MESSAGE = TextFormat::GRAY . '{winner} won an unranked match against {loser} with type {type}!';

	public function sendPlayersScoreboard(): void {
		/** @var Player[] $players */
		$players = array_values($this->players);

		if(isset($players[0]) && isset($players[1])) {
			$text1 = (array)str_replace([
			    '{fighting}',
			    '{ping}',
                '{oping}',
                '{time}'
            ], [
                $players[1]->getDisplayName(),
                $players[0]->getNetworkSession()->getPing(),
                $players[1]->getNetworkSession()->getPing(),
                Utilities::secondsToReadableTime($this->countdown)
            ], $this->duels->scoreboardArray['1v1']);

            ScoreFactory::setScore($players[0], "§l§bVersai Practice");
            $pm = PlayerManager::get($players[0]);
            $scoreboard = new Scoreboard($pm, $text1);
            $scoreboard->update()->display();
            $pm->setScoreboard($scoreboard);

			$text2 = (array)str_replace([
			    '{fighting}',
			    '{ping}',
                '{oping}',
                '{time}'
            ], [
                $players[0]->getDisplayName(),
                $players[1]->getNetworkSession()->getPing(),
                $players[0]->getNetworkSession()->getPing(),
                Utilities::secondsToReadableTime($this->countdown)
            ], $this->duels->scoreboardArray['1v1']);

            ScoreFactory::setScore($players[1], "§l§bVersai Practice");
            $pm = PlayerManager::get($players[1]);
            $scoreboard = new Scoreboard($pm, $text2);
            $scoreboard->update()->display();
            $pm->setScoreboard($scoreboard);
		}
	}

	public function sendEndMatchScoreboard(): void {
		foreach ($this->players as $player) {
            $text = (array)str_replace([
                '{opponent}'
            ], [
                array_values($this->removedPlayers)[0]->getDisplayName()
            ], $this->duels->scoreboardArray['1v1-Win']);

            ScoreFactory::setScore($player, "§l§bVersai Practice");
            $pm = PlayerManager::get($player);
            $scoreboard = new Scoreboard($pm, $text);
            $scoreboard->update()->display();
            $pm->setScoreboard($scoreboard);
        }
	}

	public function sendSpectatorsScoreboard(): void {
		foreach ($this->spectatingPlayers as $player) {
            $text = (array)str_replace([
                '{time}',
                '{spectators}'
            ], [
                Utilities::secondsToReadableTime($this->countdown),
                count($this->spectatingPlayers)
            ], $this->duels->scoreboardArray['1v1-Spectate']);

            ScoreFactory::setScore($player, "§l§bVersai Practice");
            $pm = PlayerManager::get($player);
            $scoreboard = new Scoreboard($pm, $text);
            $scoreboard->update()->display();
            $pm->setScoreboard($scoreboard);
        }
	}


    /**
     * @return null|Player
     */
	public function getLoser(): ?Player{
        $ak = array_keys($this->removedPlayers);

        if(!isset($ak[0])) {
            return null;
        }
        if($this->stage !== Heartbeat::STAGE_FINISHED) {
            return null;
        }

        return array_values($this->removedPlayers)[0];
    }

}