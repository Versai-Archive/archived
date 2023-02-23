<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Party;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\SubCommand;

class PartyHelp extends SubCommand {

	private const PREFIX = TextFormat::GREEN . '/party ';

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$sender->sendMessage(TextFormat::GREEN . 'Parties!');
		$sender->sendMessage(PartyHelp::PREFIX . '- Brings up the menu');
		$sender->sendMessage(PartyHelp::PREFIX . '(c)reate - Creates a party');
		$sender->sendMessage(PartyHelp::PREFIX . '(di)sband - Disbands your party');
		$sender->sendMessage(PartyHelp::PREFIX . '(a)ccept - Accepts an invitation');
		$sender->sendMessage(PartyHelp::PREFIX . '(l)eave - Leave a party');
		$sender->sendMessage(PartyHelp::PREFIX . '(li)st - List open parties');
		$sender->sendMessage(PartyHelp::PREFIX . '(li)stplayers - List players in your party');
		$sender->sendMessage(PartyHelp::PREFIX . '(o)pen - Open your party');
		$sender->sendMessage(PartyHelp::PREFIX . '(c)lose - Close your party');
		$sender->sendMessage(PartyHelp::PREFIX . '(j)oin <code> - Join a party via code');
		$sender->sendMessage(PartyHelp::PREFIX . '(k)ick <player> - Kick a player from your party');
		$sender->sendMessage(PartyHelp::PREFIX . '(p)romote <player> - Promote a player to party leader');
		$sender->sendMessage(PartyHelp::PREFIX . '(d)uel - Duel with your party');
		$sender->sendMessage(PartyHelp::PREFIX . '(s)pectate - Spectate your party');
		$sender->sendMessage(PartyHelp::PREFIX . '(h)elp - Brings up this help menu');
		$sender->sendMessage(TextFormat::GREEN . 'You can also do ' . Constants::PARTY_CHAT_SYMBOL . ' at the beginning of a message to talk within your party!');
	}
}