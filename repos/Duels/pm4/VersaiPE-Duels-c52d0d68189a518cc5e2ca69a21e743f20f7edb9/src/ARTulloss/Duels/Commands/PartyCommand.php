<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/7/2019
 * Time: 11:23 AM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands;

use ARTulloss\Duels\Commands\Sub\Party\PartyDuel;
use ARTulloss\Duels\Commands\Sub\Party\PartyListPlayers;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

use jojoe77777\FormAPI\SimpleForm;

use ARTulloss\Duels\Commands\Sub\Party\PartyCreate;
use ARTulloss\Duels\Commands\Sub\Party\PartyDisband;
use ARTulloss\Duels\Commands\Sub\Party\PartyJoin;
use ARTulloss\Duels\Commands\Sub\Party\PartyLeave;
use ARTulloss\Duels\Commands\Sub\Party\PartyAccept;
use ARTulloss\Duels\Commands\Sub\Party\PartyInvite;
use ARTulloss\Duels\Commands\Sub\Party\PartyOpen;
use ARTulloss\Duels\Commands\Sub\Party\PartyClose;
use ARTulloss\Duels\Commands\Sub\Party\PartyList;
use ARTulloss\Duels\Commands\Sub\Party\PartyHelp;
use ARTulloss\Duels\Commands\Sub\Party\PartyKick;
use ARTulloss\Duels\Commands\Sub\Party\PartyPromote;
use ARTulloss\Duels\Commands\Sub\Party\PartySpectate;
use ARTulloss\Kits\Kits;
use ARTulloss\Duels\Duels;

use function strtolower;

/**
 * Class PartyCommand
 * @package ARTulloss\Duels\Commands
 */
class PartyCommand extends Command
{
	/** @var Player[] */
	public $invitedPlayers;
	/** @var Kits $kits */
	private $kits;
    /**
     * @var Plugin
     */
    private Plugin $own;

    /**
	 * PartyCommand constructor.
	 * @param $name
	 * @param Plugin $owner
	 * @param Kits $kits
	 */
	public function __construct($name, Plugin $owner, Kits $kits)
	{
		parent::__construct($name);

		$this->own = $owner;

		$this->setUsage('Do /party (h)elp for help with commands!');
		$this->setDescription('Play with friends!');
		$this->setAliases(['p']);
		$this->kits = $kits;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void
	{
		/** @var Duels $plugin */
		$plugin = $this->getPlugin();

		if($sender instanceof Player) {

			$manager = $plugin->partyManager;

			if ($sender->getLevel() !== $plugin->getServer()->getDefaultLevel())
				$sender->sendMessage(TextFormat::RED . 'You need to be in the lobby to use duels!');
			elseif(isset($args[0])) {
					switch (strtolower($args[0])) {
						case 'create':
						case  'c':
							$sub = new PartyCreate($manager);
							break;
						case 'disband':
						case 'dis':
						case 'di':
							$sub = new PartyDisband($manager);
							break;
						case 'public':
						case 'open':
						case 'o':
							$sub = new PartyOpen($manager);
							break;
						case 'private':
						case 'close':
						case 'cl':
							$sub = new PartyClose($manager);
							break;
						case 'join':
						case 'j':
							$sub = new PartyJoin($manager);
							break;
						case 'invite':
						case 'i':
							$sub = 	new PartyInvite($manager, $this);
							break;
						case 'a':
						case 'accept':
							$sub = new PartyAccept($manager, $this);
							break;
						case 'l':
						case 'leave':
							$sub = new PartyLeave($manager);
							break;
						case 'kick':
							$sub = new PartyKick($manager);
							break;
						case 'li':
						case 'list':
							$sub = new PartyList($manager);
							break;
						case 'p':
						case 'promote':
							$sub = new PartyPromote($manager);
							break;
						case 'spec':
						case 's':
						case 'spectate':
							$sub = new PartySpectate($manager, $this);
							break;
						case 'd':
						case 'duel':
							$sub = new PartyDuel($manager);
							break;
						case 'listplayers':
						case 'listall':
							$sub = new PartyListPlayers($manager, false, $this);
							break;
						case 'h':
						case 'help':
							$sub = new PartyHelp($manager);
							break;
						default:
							throw new InvalidCommandSyntaxException();
					}

					array_shift($args);
					$sub->execute($sender, (array)$args);

					} else {

					$party = $manager->getPartyForPlayer($sender);

					if($party === null)
						$this->sendNotInPartyForm($sender);
					elseif($party->getLeader() === $sender)
						$this->sendPartyOwnerForm($sender);
					else
						$this->sendInPartyForm($sender);
			}
		}

	}

	/**
	 * @param Player $player
	 */
	public function sendNotInPartyForm(Player $player): void
	{
		/**
		 * @param Player $player
		 * @param $data
		 */
		$callable = function (Player $player, $data): void
		{
			if(isset($data)) {

				/** @var Duels $duels */
				$duels =  $this->getPlugin();
				$manager = $duels->partyManager;

				switch ($data) {

					case 0:
						$sub = new PartyCreate($manager);
						break;
					case 1:
						$sub = new PartyList($manager, true);
						break;
					case 2:
						$sub = new PartyHelp($manager);

				}

				if(isset($sub))
					$sub->execute($player, []);

			}
		};

		$form = new SimpleForm($callable);

		$form->setTitle('Party');

		$form->addButton('Create');
		$form->addButton('List');
		$form->addButton('Help');

		$player->sendForm($form);

	}

	/**
	 * @param Player $player
	 */
	public function sendInPartyForm(Player $player): void
	{
		/**
		 * @param Player $player
		 * @param $data
		 */
		$callable = function (Player $player, $data): void
		{
			if(isset($data)) {

				/** @var Duels $duels */
				$duels =  $this->getPlugin();
				$manager = $duels->partyManager;

				switch ($data) {

					case 0:
						$sub = new PartyListPlayers($manager, true, $this);
						break;
					case 1:
						$sub = new PartySpectate($manager, $this);
						break;
					case 2:
						$sub = new PartyLeave($manager);
				}
				
				if(isset($sub))
					$sub->execute($player, []);
				
			}
		};

		$form = new SimpleForm($callable);

		$form->setTitle('Party');

		$form->addButton('Players');
		$form->addButton('Spectate');
		$form->addButton('Leave');

		$player->sendForm($form);

	}

	public function sendPartyOwnerForm(Player $player): void
	{
		/**
		 * @param Player $player
		 * @param $data
		 */
		$callable = function (Player $player, $data): void
		{
			if(isset($data)) {

				/** @var Duels $duels */
				$duels =  $this->getPlugin();
				$manager = $duels->partyManager;

				switch ($data) {

					case 0:
						$sub = new PartyDuel($manager, true);
						break;

					case 1:
						$sub = new PartySpectate($manager, $this);
						break;

					case 2:
						$sub = new PartyOpen($manager);
						break;

					case 3:
						$sub = new PartyClose($manager);
						break;

					case 4:
						$sub = new PartyDisband($manager);
						break;

					case 5:
						$sub = new PartyPromote($manager, true);
						break;

					case 6:
						$sub = new PartyListPlayers($manager, true, $this);
						break;

					case 7:
						$sub = new PartyList($manager, true);

				}

				if(isset($sub))
					$sub->execute($player, []);

			}
		};

		$form = new SimpleForm($callable);

		$form->setTitle('Party Leader Options');

		$form->addButton('Duel!');
		$form->addButton('Spectate');
		$form->addButton('Open');
		$form->addButton('Close');
		$form->addButton('Disband');
		$form->addButton('Promote');
		$form->addButton('List Players');
		$form->addButton('List Parties');

		$player->sendForm($form);

	}

	public function getPlugin(): Plugin
    {

	    return $this->own;

    }

}