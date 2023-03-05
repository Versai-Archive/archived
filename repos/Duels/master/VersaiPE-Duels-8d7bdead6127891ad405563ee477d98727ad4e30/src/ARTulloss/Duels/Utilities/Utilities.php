<?php
declare(strict_types=1);

namespace ARTulloss\Duels\Utilities;

use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use Throwable;
use Closure;

/**
 * Class Utilities
 * @package ARTulloss\Duels\Utilities
 */
class Utilities {
	/**
	 * @param Player $sender
	 * @param string $playerName
	 * @return null|Player
	 */
	static public function getPlayerCommand(Player $sender, string $playerName): ?Player{
		$player = self::getPlayer($playerName);
		if ($player === null) {
			$sender->sendMessage(TextFormat::RED . 'Player isn\'t online');
			return null;
		}
		return $player;
	}
	/**
	 * Get a player name as best as possible
	 * @param string $name
	 * @return null|Player
	 */
	static public function getPlayer(string $name): ?Player{
		$player = self::getDisguisedPlayerExact($name);
		if($player === null)
			$player = static::getDisguisedPlayer($name);
		return $player;
	}
	/**
	 * Will work regardless of if they are disguised
	 * @param string $name
	 * @return null|Player
	 */
	static public function getDisguisedPlayer(string $name): ?Player{
		$found = null;
		$name = strtolower($name);
		$delta = PHP_INT_MAX;
		foreach(Server::getInstance()->getOnlinePlayers() as $player) {
			$displayName = $player->getDisplayName();
			if(stripos($displayName, $name) === 0) {
				$curDelta = strlen($displayName) - strlen($name);
				if($curDelta < $delta){
					$found = $player;
					$delta = $curDelta;
				}
				if($curDelta === 0)
					break;
			}
		}
		return $found;
	}
	/**
	 * Will work regardless of if they are disguised
	 * @param string $name
	 * @return null|Player
	 */
	static public function getDisguisedPlayerExact(string $name): ?Player{
		foreach (Server::getInstance()->getOnlinePlayers() as $player)
			if($player->getDisplayName() === $name)
				return $player;
		return null;
	}
	/**
	 * Convert a number of seconds into a time format
	 * Example: 75 -> 1:15
	 *
	 * @param int $seconds
	 * @return string
	 */
	static public function secondsToReadableTime(int $seconds): string{
		$mins = $seconds / 60;
		$rmins = (int) floor($mins);
		$newSeconds = (int) (($mins - $rmins) * 60);

		if(strlen((string)$newSeconds) === 1)
			$newSeconds = '0' . (string) $newSeconds;

		$result = $rmins . ':' . $newSeconds;

		return $result;
	}
    /**
     * @param Plugin|null $plugin
     * @return Closure
     */
    public static function getOnError(Plugin $plugin = null): Closure{
        return function (Throwable $error) use ($plugin): void{
            $hasLogger = $plugin !== null ? $plugin : Server::getInstance();
            $hasLogger->getLogger()->logException($error);
        };
    }
}