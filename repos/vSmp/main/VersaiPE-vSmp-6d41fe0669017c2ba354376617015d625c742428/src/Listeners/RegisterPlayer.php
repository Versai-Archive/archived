<?php

declare(strict_types=1);

namespace Versai\RPGCore\Listeners;

use pocketmine\player\Player;
use pocketmine\event\Listener;
use Versai\RPGCore\Libraries\FormAPI\window\SimpleWindowForm;
use Versai\RPGCore\Libraries\FormAPI\elements\Button;
use pocketmine\event\player\PlayerJoinEvent;
use Versai\RPGCore\Main;

class RegisterPlayer implements Listener
{

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function playerJoinEvent(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();

        $sessionManager = $this->plugin->getSessionManager();
        $session = $sessionManager->getSession($player);
        $window = new SimpleWindowForm("class_selector", "§aClass Selector", "§2Select what class you would like to be §c ( YOU WILL NOT BE ABLE TO CHANGE THIS )", function (Player $player, Button $btn) use ($session) {
            switch ($btn->getText()) {
                case "§7Warrior":
                    $session->setClass("warrior");
                    $session->setAgility(0.12);
                    $session->setMaxMana(10);
                    $session->setDefense(8);
                    $session->setCoins(0);
                    $session->setQuestId(1);
                    $session->setQuestProgress(0);
                    $player->sendMessage("§aCongrats you are now a §e" . $btn->getText() . "§a!!!");
                    break;

                case "§0Rouge":
                    $session->setClass("rouge");
                    $session->setAgility(0.15);
                    $session->setMaxMana(7);
                    $session->setDefense(4);
                    $session->setCoins(0);
                    $session->setQuestId(1);
                    $session->setQuestProgress(0);
                    $player->sendMessage("§aCongrats you are now a §e" . $btn->getText() . "§a!!!");
                    break;

                case "§9Summoner":
                    $session->setClass("summoner");
                    $session->setAgility(0.12);
                    $session->setMaxMana(15);
                    $session->setDefense(8);
                    $session->setCoins(0);
                    $session->setQuestId(1);
                    $session->setQuestProgress(0);
                    $player->sendMessage("§aCongrats you are now a §e" . $btn->getText() . "§a!!!");
                    break;

                case "§6Necromancer":
                    $session->setClass("necromancer");
                    $session->setAgility(0.10);
                    $session->setMaxMana(25);
                    $session->setDefense(3);
                    $session->setCoins(0);
                    $session->setQuestId(1);
                    $session->setQuestProgress(0);
                    $player->sendMessage("§aCongrats you are now a §e" . $btn->getText() . "§a!!!");
                    break;

                case "§eHealer":
                    $session->setClass("necromancer");
                    $session->setAgility(0.12);
                    $session->setMaxMana(10);
                    $session->setDefense(5);
                    $session->setCoins(0);
                    $session->setQuestId(1);
                    $session->setQuestProgress(0);
                    $player->sendMessage("§aCongrats you are now a §e" . $btn->getText() . "§a!!!");
                    break;

                case "§7Paladin":
                    $session->setClass("necromancer");
                    $session->setAgility(0.10);
                    $session->setMaxMana(5);
                    $session->setDefense(10);
                    $session->setCoins(0);
                    $session->setQuestId(1);
                    $session->setQuestProgress(0);
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

