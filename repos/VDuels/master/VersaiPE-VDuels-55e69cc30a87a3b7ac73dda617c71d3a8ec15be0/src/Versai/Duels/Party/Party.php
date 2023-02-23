<?php
declare(strict_types=1);

namespace Versai\Duels\Party;

use pocketmine\player\Player;
use Versai\Duels\Duels;
use Versai\Duels\Utilities\Utilities;

class Party {

	/** @var Player $leader */
	private Player $leader;
	/** @var Player[] */
	private array $players;
	/** @var string $code */
	private string $code;
	/** @var bool $public */
	private bool $public;

	/**
	 * Party constructor.
	 * @param Player $leader
	 */
	public function __construct(Player $leader) {
		$this->leader = $leader;
		$this->players = [$leader->getName() => $leader];

		$tick = $leader->getServer()->getTick();

		$this->code = base64_encode((string)$tick);
		$this->public = false;

	}

	/**
	 * @param Player $player
	 */
	public function setLeader(Player $player): void {
		$this->leader = $player;
	}

	/**
	 * @return Player
	 */
	public function getLeader(): Player {
	//	var_dump($this);
		return $this->leader;
	}

	/**
	 * @return Player[]
	 */
	public function getPlayers(): array {
		return $this->players;
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function addPlayer(Player $player): bool {
	//	var_dump($this);
		$name = $player->getName();
		if(isset($this->players[$name])) {
            return false;
        }
		$this->players[$name] = $player;
		return true;
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function removePlayer(Player $player): bool {
	//	var_dump($this);
		$name = $player->getName();
		if(isset($this->players[$name])) {
			unset($this->players[$name]);
			return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function getCode(): string {
	//	var_dump($this);
		return $this->code;
	}

	/**
	 * @return bool
	 */
	public function isPublic(): bool {
	//	var_dump($this);
		return $this->public;
	}

	/**
	 * @param bool $state
	 */
	public function setPublic(bool $state = true): void {
		$this->public = $state;
	//	var_dump($this);
	}

	/**
	 * @param string $message
	 */
	public function sendMessageToNonLeader(string $message): void {
		// Message the players, but not the leader

		$leader = $this->leader;

		foreach($this->players as $player) {
            if ($player !== $leader && $player->isOnline()) {
                $player->sendMessage($message);
            }
        }
	}

	/**
	 * @param string $message
	 */
	public function sendMessageToAll(string $message): void {
		foreach($this->players as $player) {
            if ($player->isOnline()) {
                $player->sendMessage($message);
            }
        }
	}

	/**
	 * @param Player $player
	 */
	public function updateNameKey(Player $player): void{
		if(!Utilities::change_key($this->players, $player->getName(), $player->getDisplayName()))
			Duels::getInstance()->getLogger()->error('Failed to update party for changed disguise!');
	}
}