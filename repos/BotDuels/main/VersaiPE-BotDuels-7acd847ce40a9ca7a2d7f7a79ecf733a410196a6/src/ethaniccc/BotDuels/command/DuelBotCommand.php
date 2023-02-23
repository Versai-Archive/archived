<?php

namespace ethaniccc\BotDuels\command;

use ethaniccc\BotDuels\bots\ComboBot;
use ethaniccc\BotDuels\bots\NoDebuffBot;
use ethaniccc\BotDuels\bots\SumoBot;
use ethaniccc\BotDuels\BotDuels;
use ethaniccc\BotDuels\game\DuelGame;
use ethaniccc\BotDuels\game\GameManager;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class DuelBotCommand extends Command implements PluginIdentifiableCommand {

	public function __construct() {
		parent::__construct("botduel", "Duel a bot", "/botduel", []);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if ($sender instanceof Player) {
			if (GameManager::getInstance()->isInGame($sender->getName())) {
				$sender->sendMessage(TextFormat::RED . "Error: You already are in a game.");
			} else {
				$form = new SimpleForm(function (Player $player, $data): void {
					if($data !== null) {
						switch($data){
                            case 0:
                            case 1:
                            case 2:
                                $this->sendBotDuelTypeForm($player, $data);
                                break;
                            default:
                                break;
                        }
					}
				});
				$form->setTitle("Choose bot duel type");
				$form->addButton(TextFormat::BLUE . "NoDebuff");
				$form->addButton(TextFormat::BLUE . "Sumo");
				$form->addButton(TextFormat::BLUE . "Combo");
				$sender->sendForm($form);
			}
		}
	}

	//Im going to redo all of this soon. Just to make it optimised in future.
	public function sendBotDuelTypeForm(Player $player, int $type){
	    switch($type){
            case BotDuels::TYPE_NODEBUFF:
                $form = new SimpleForm(function (Player $player, $data) use ($type): void {
                    if ($data !== null) {
                        $difficulty = max(min($data, NoDebuffBot::DIFFICULTY_BLATANT_HACKER), NoDebuffBot::DIFFICULTY_EASY);
                        GameManager::getInstance()->add(new DuelGame($player, $type, $difficulty, BotDuels::getInstance()->getRandomMap(BotDuels::TYPE_NODEBUFF)));
                    }
                });
                $data = $this->getPlugin()->getConfig()->getNested("bot-data.nodebuff");
                $form->setTitle("Choose difficulty");
                foreach($data as $type => $botData) {
                    $form->addButton(TextFormat::BLUE . $botData["name"]);
                }
                $player->sendForm($form);
                break;
            case BotDuels::TYPE_SUMO:
                $form = new SimpleForm(function (Player $player, $data) use ($type): void {
                    if ($data !== null) {
                        $difficulty = max(min($data, SumoBot::DIFFICULTY_EXPERT), SumoBot::DIFFICULTY_EASY);
                        GameManager::getInstance()->add(new DuelGame($player, $type, $difficulty, BotDuels::getInstance()->getRandomMap(BotDuels::TYPE_SUMO)));
                    }
                });
                $data = $this->getPlugin()->getConfig()->getNested("bot-data.sumo");
                $form->setTitle("Choose difficulty");
                foreach($data as $type => $botData) {
                    $form->addButton(TextFormat::BLUE . $botData["name"]);  // Attempt to make it automatic.
                }
                $player->sendForm($form);
                break;
            case BotDuels::TYPE_COMBO:
                $form = new SimpleForm(function (Player $player, $data) use ($type): void {
                    if ($data !== null) {
                        $difficulty = max(min($data, ComboBot::DIFFICULTY_HARD), ComboBot::DIFFICULTY_EASY);
                        GameManager::getInstance()->add(new DuelGame($player, $type, $difficulty, BotDuels::getInstance()->getRandomMap(BotDuels::TYPE_COMBO)));
                    }
                });
                $data = $this->getPlugin()->getConfig()->getNested("bot-data.combo");
                $form->setTitle("Choose difficulty");
                foreach($data as $type => $botData) {
                    $form->addButton(TextFormat::BLUE . $botData["name"]);  // Attempt to make it automatic.
                }
                $player->sendForm($form);
                break;
        }
    }

	public function getPlugin(): Plugin {
		return BotDuels::getInstance();
	}

}