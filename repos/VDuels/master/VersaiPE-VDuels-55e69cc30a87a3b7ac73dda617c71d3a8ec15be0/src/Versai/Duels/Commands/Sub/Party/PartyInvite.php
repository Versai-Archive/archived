<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Party;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\SubCommand;
use Versai\Duels\Utilities\Utilities;

class PartyInvite extends SubCommand {

	private const TYPE = 'player';
	private const ACTION = 'invite';

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$party = $this->manager->getPartyForPlayer($sender);

		if(!isset($args[0])) {
            $sender->sendMessage(str_replace('{type}', self::TYPE, Constants::MUST_ENTER));
        } elseif($this->manager->checkPartyLeader($sender, $party)) {

			$player = Utilities::getPlayerCommand($sender, $args[0]);

			if($player === null) { // Utility handles message
                return;
            }

			if ($sender === $player) {
                $sender->sendMessage(str_replace('{action}', self::ACTION, Constants::ACTION_SELF));
            } elseif (($party = $this->manager->getPartyForPlayer($player)) && $party !== null && !in_array($player, $party->getPlayers(), true)) {
                $sender->sendMessage(Constants::PLAYER_ALREADY_IN_PARTY);
            } else {
				$sender->sendMessage(TextFormat::GREEN . 'You invited ' . $player->getDisplayName() . ' to your party!');
				$this->manager->sendRequestMessage($sender, $player);
				$this->command->invitedPlayers[$sender->getName()] = $player;
			}
		}

	}
}