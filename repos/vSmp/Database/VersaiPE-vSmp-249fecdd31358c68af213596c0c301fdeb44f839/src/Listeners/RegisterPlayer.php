<?php

declare(strict_types=1);

namespace Versai\RPGCore\Listeners;

use pocketmine\player\Player;
use pocketmine\event\Listener;
use Versai\RPGCore\Libraries\FormAPI\window\SimpleWindowForm;
use Versai\RPGCore\Libraries\FormAPI\elements\Button;
use pocketmine\event\player\PlayerJoinEvent;
use Versai\RPGCore\Main;
use Versai\RPGCore\Data\SQLDataStorer;

class RegisterPlayer implements Listener {

    private $plugin;

    public function __construct(Main $plugin) {
		$this->plugin = $plugin;
    }

    public function playerJoinEvent(PlayerJoinEvent $event) {
        $player = $event->getPlayer();

        $dataStorer = new SQLDataStorer($this->plugin);

        $class = $dataStorer->getClass($player);

        if($class) {
            var_dump($class);
            return;
        }

        $sessionManager = $this->plugin->getSessionManager();
        $playerSession = $sessionManager->getSession($player);
        $window = new SimpleWindowForm("class_selector", "§aClass Selector", "§2Select what class you would like to be §c ( YOU WILL NOT BE ABLE TO CHANGE THIS )", function(Player $player, Button $btn) {
            // set the stats according to the class selected
            // add other data

            $sessionManager = $this->plugin->getSessionManager();
            $playerSession = $sessionManager->getSession($player);

            switch($btn->getText()) {
                case "§7Warrior":
                    $playerSession->setClass("warrior");
                    $playerSession->setAgility(0.12);
                    $playerSession->setMaxMana(10);
                    $playerSession->setDefense(8);
                    $playerSession->setCoins(0);
                    $playerSession->setQuestId(1);
                    $playerSession->setQuestProgress(0);
                    $player->sendMessage("§aCongrats you are now a §e" . $btn->getText() . "§a!!!");
                break;

                case "§0Rouge":
                    $playerSession->setClass("rouge");
                    $playerSession->setAgility(0.15);
                    $playerSession->setMaxMana(7);
                    $playerSession->setDefense(4);
                    $playerSession->setCoins(0);
                    $playerSession->setQuestId(1);
                    $playerSession->setQuestProgress(0);
                    $player->sendMessage("§aCongrats you are now a §e" . $btn->getText() . "§a!!!");
                break;

                case "§9Summoner":
                    $playerSession->setClass("summoner");
                    $playerSession->setAgility(0.12);
                    $playerSession->setMaxMana(15);
                    $playerSession->setDefense(8);
                    $playerSession->setCoins(0);
                    $playerSession->setQuestId(1);
                    $playerSession->setQuestProgress(0);
                    $player->sendMessage("§aCongrats you are now a §e" . $btn->getText() . "§a!!!");
                break;

                case "§6Necromancer":
                    $playerSession->setClass("necromancer");
                    $playerSession->setAgility(0.10);
                    $playerSession->setMaxMana(25);
                    $playerSession->setDefense(3);
                    $playerSession->setCoins(0);
                    $playerSession->setQuestId(1);
                    $playerSession->setQuestProgress(0);
                    $player->sendMessage("§aCongrats you are now a §e" . $btn->getText() . "§a!!!");
                break;

                case "§eHealer":
                    $playerSession->setClass("necromancer");
                    $playerSession->setAgility(0.12);
                    $playerSession->setMaxMana(10);
                    $playerSession->setDefense(5);
                    $playerSession->setCoins(0);
                    $playerSession->setQuestId(1);
                    $playerSession->setQuestProgress(0);
                    $player->sendMessage("§aCongrats you are now a §e" . $btn->getText() . "§a!!!");
                break;

                case "§7Paladin":
                    $playerSession->setClass("necromancer");
                    $playerSession->setAgility(0.10);
                    $playerSession->setMaxMana(5);
                    $playerSession->setDefense(10);
                    $playerSession->setCoins(0);
                    $playerSession->setQuestId(1);
                    $playerSession->setQuestProgress(0);
                    $player->sendMessage("§aCongrats you are now a §e" . $btn->getText() . "§a!!!");
                break;
            }
        });

        $window->addButton("warrior", "§7Warrior");
        $window->addButton("rouge", "§0Rouge");
        $window->addButton("summoner", "§9Summoner");
        $window->addButton("necromancer", "§6Necromancer");
        $window->addButton("healer", "§eHealer");
        $window->addButton("paladin", "§7Paladin");

        $window->showTo($player);
    }
}