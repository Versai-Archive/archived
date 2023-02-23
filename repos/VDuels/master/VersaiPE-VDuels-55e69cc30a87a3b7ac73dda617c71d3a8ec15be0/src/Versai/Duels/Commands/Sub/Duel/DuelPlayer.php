<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Duel;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\SubCommand;
use Versai\Duels\Duels;
use jojoe77777\FormAPI\SimpleForm;
use Versai\Duels\Utilities\Utilities;
use Duo\kits\Kit;
use Duo\kits\Kits;
use function array_values;
use function count;
use function in_array;
use function mb_strtolower;
use function strtolower;

class DuelPlayer extends SubCommand {

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$asked = Utilities::getPlayer($args[0]);

		if ($asked !== null) {
			if ($asked === $sender) {
                $sender->sendMessage(TextFormat::RED . 'You can\'t duel yourself!');
            }else {
                $this->sendRequestDuelMenu($sender, $asked);
            }
		} else {
            $sender->sendMessage(TextFormat::RED . "Unfortunately no player by the name of $args[0] is online!");
        }
	}

	/**
	 * @param Player $player
	 * @param Player $asked
	 */
	public function sendRequestDuelMenu(Player $player, Player $asked): void
	{
		$kits = $this->command->getKits();

		$kitTypes = $kits->kitTypes;

		$levels = Duels::getInstance()->levels;

		$callable = function (Player $player, $selection1) use ($asked, $kitTypes, $levels): void {
            if (isset($selection1)) {
                $type = array_values($kitTypes)[$selection1];
                $callable = function (Player $player, $selection2) use ($asked, $kitTypes, $levels, $type, $selection1) {
                    if(isset($selection2)) {
                        $askerName = $player->getName();
                        $allKitIds = Kits::getInstance()->kitIDs;
                        $kitId = $allKitIds[mb_strtolower($type)];
                        foreach ($levels as $key => $level) {
                            if (!in_array($kitId, $level->getIDs())) {
                                unset($levels[$key]);
                            }
                        }
                        if ($selection2 > count($levels)) { // Back button
                            $this->sendRequestDuelMenu($player, $asked);
                            return;
                        }
                        $map = $selection2 === 0 ? 'Random' : array_values($levels)[--$selection2]->getName();
                        $this->command->removeAllDuelRequests($asked);
                        $this->command->askedForDuel[implode(':', [$askerName, $type, $map])] = $asked;
						try {
							// This is first because it will throw an exception if the player is no longer online
							$asked->sendMessage(TextFormat::GREEN . "You've been invited to a duel by " . $player->getDisplayName() . " with type $type on map $map! To accept, do /duel accept.");
							$player->sendMessage(TextFormat::GREEN . 'You invited ' . $asked->getDisplayName() . " to a $type duel!");
						} catch(\LogicException $e) {
							$player->sendMessage(TextFormat::RED . 'The player you invited to duel is no longer online!');
						}
                    };
			    };
			    $form = new SimpleForm($callable);
			    $form->setTitle('Select an Arena');
			    $form->addButton('Random');
                $allKitIds = Kits::getInstance()->kitIDs;
                $kitId = $allKitIds[mb_strtolower($type)];
			    foreach ($levels as $key => $level) {
                    if(in_array($kitId, $level->getIDs())) {
                        $form->addButton($level->getName());
                    }
                }
			    $form->addButton(Constants::BACK, Constants::BACK_TYPE, Constants::BACK_IMAGE);
			    $player->sendForm($form);
			}
		};

		$form = new SimpleForm($callable);

		foreach ($kitTypes as $kitType) {
			$lKitType = strtolower($kitType);
			/** @var Kit $kitValues */
			$kit = $kits->kits[$lKitType];
			$form->addButton($kitType, $kit->getImageType(), $kit->getURL());
		}

		$form->setTitle("Select a type!");

		$player->sendForm($form);

	}

}