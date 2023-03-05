<?php
declare(strict_types=1);

namespace Versai\Duels\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\Duels\Duels;
use jojoe77777\FormAPI\SimpleForm;
use Versai\Duels\Match\Task\Heartbeat;
use Versai\Duels\Match\Task\PartyHeartbeat;
use Versai\Duels\Utilities\Utilities;

class SpectateCommand extends Command {

	/**
	 * SpectateCommand constructor.
	 * @param string $name
	 */
	public function __construct(string $name){
		parent::__construct($name, 'Spectate duels!');
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return bool|mixed|void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof Player) {
			/** @var Duels $duels */
			$duels = Duels::getInstance();

			if ($sender->getWorld() !== $duels->getServer()->getWorldManager()->getDefaultWorld() && $duels->duelManager->getSpectatorsMatch($sender) === null) {
				$sender->sendMessage(TextFormat::RED . 'You need to be in the lobby or spectating to use /spectate');
				return;
			}

			if(isset($args[0])) {
                $player = Utilities::getPlayer($args[0]);
                if($player === null) {
                    $sender->sendMessage(TextFormat::RED . 'That player is offline!');
                    return;
                }
                if($player === $sender) {
                    $sender->sendMessage(TextFormat::RED . 'You can\'t spectate yourself!');
                    return;
                }
                $match = $duels->duelManager->getPlayersMatch($player);
                if($match !== null) {
                    $match->addSpectator($sender);
                }else {
                    $sender->sendMessage(TextFormat::RED . 'That player isn\'t in a match!');
                }
                return;
            }

			$matches = $duels->duelManager->getAllRunningMatchHeartbeats();

			$callable = function (Player $player, $data) use ($matches): void
			{
				if(isset($data) && $matches !== []) {
					$match = $matches[$data];

					if ($match->getStage() === Heartbeat::STAGE_FINISHED) {
                        $player->sendMessage(Constants::SPECTATE_FINISHED);
                    } else {
						if(isset($match->getSpectators()[$player->getName()])) {
                            $player->sendMessage(TextFormat::RED . 'You are already spectating that match!');
                        } else {
                            $match->addSpectator($player);
                        }
					}
				}
			};

			$form = new SimpleForm($callable);

			$form->setTitle('Spectate menu!');

			if($matches !== []) {

				foreach ($matches as $heartbeat) {

					$playerNames = array_keys($heartbeat->getPlayers());

					if(!isset($playerNames[1])) { // If the second player is dead, get the first removed players
						$playerNames[1] = TextFormat::RED . array_keys($heartbeat->getRemovedPlayers())[0];
					}

					$ranked = $duels->queueManager->translateQueue($heartbeat->getRanked(), true);
					$stage = $heartbeat->getStage();

					$status = '';

					switch ($stage) {
						case Heartbeat::STAGE_COUNTDOWN:
							$status = 'Countdown';
							break;
						case Heartbeat::STAGE_PLAYING:
							$status = 'Playing';
							break;
						case Heartbeat::STAGE_FINISHED:
							$status = 'Finished';
					}

					if($heartbeat instanceof PartyHeartbeat) {
                        $players = TextFormat::BLUE . 'Party - ' . $heartbeat->getParty()->getLeader()->getDisplayName();
                    } else {
                        $players = TextFormat::GOLD . $playerNames[0] . ' vs ' . $playerNames[1];
                    }
					$form->addButton($players . "\n" . TextFormat::DARK_AQUA . $status . ' - ' . $ranked . ' ' . $heartbeat->getKitType());

				}
			} else {
                $form->addButton('No matches running!');
            }

			$sender->sendForm($form);

		} else {
            $sender->sendMessage(TextFormat::RED . 'You need to be a player to execute this command!');
        }
	}
}