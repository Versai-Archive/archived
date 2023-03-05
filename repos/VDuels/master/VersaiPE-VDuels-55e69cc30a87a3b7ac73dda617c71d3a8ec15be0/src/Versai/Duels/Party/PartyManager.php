<?php
declare(strict_types=1);

namespace Versai\Duels\Party;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\Duels\Manager;

class PartyManager implements Manager {

	/** @var Party[] */
	private array $parties = [];

	/**
	 * @param Player $player
	 * @return Party
	 */
	public function createParty(Player $player): Party {
		$party = new Party($player);
		$this->parties[$player->getName()] = $party;
		return $party;
	}

	/**
	 * @param Player $player
	 * @param Party $party
	 */
	public function promotePlayer(Player $player, Party $party): void {
		$playerName = $player->getName();
		$oldLeaderName = $party->getLeader()->getName();
		$this->parties[$playerName] = $this->parties[$oldLeaderName];
		$this->parties[$playerName]->setLeader($player);
		unset($this->parties[$oldLeaderName]);
	}

	/**
	 * @param Party $party
	 * @param Player $leader
	 */
	public function disbandParty(Party $party, Player $leader = null): void
	{
		if($leader === null)
			$leader = $party->getLeader();
	//	echo '\nDISBANDED PARTY';
		unset($this->parties[$leader->getName()]);

	}

	/**
	 * @param Player $inviter
	 * @param Player $invited
	 */
	public function sendRequestMessage(Player $inviter, Player $invited): void
	{
		$invited->sendMessage(TextFormat::GREEN . $inviter->getDisplayName() . ' invited you to a party. Do /party accept to join!');
	}

	/**
	 * @param Player $player
	 * @return Party|null
	 */
	public function getPartyForPlayer(Player $player): ?Party
	{
		$name = $player->getName();
		return $this->getPartyFor($name);
	}

	/**
	 * @param string $name
	 * @return Party|null
	 */
	public function getPartyFor(string $name): ?Party
	{
	//	var_dump($this->parties);
		foreach ((array)$this->parties as $party) {
			if(isset($party->getPlayers()[$name]))
				return $party;
		}
		return null;
	}

	/**
	 * @param string $code
	 * @return Party|null
	 */
	public function getPartyByCode(string $code): ?Party
	{
		foreach ((array)$this->parties as $party) {
			if($code === $party->getCode())
				return $party;
		}
		return null;
	}

	/**
	 * @return Party[]|null
	 */
	public function getAllParties(): ?array
	{
		return $this->parties;
	}

	/**
	 * @param Player $player
	 * @param null $party
	 * @return bool
	 */
	public function checkPartyLeader(Player $player, $party = null) {
		if($party instanceof Party) {
				if($party->getLeader() === $player)
					return true;
				$player->sendMessage(TextFormat::RED . 'You have to be the party leader to do that!');
			} else
				$player->sendMessage(TextFormat::RED . 'You\'re not in a party!');
		return false;
	}

}