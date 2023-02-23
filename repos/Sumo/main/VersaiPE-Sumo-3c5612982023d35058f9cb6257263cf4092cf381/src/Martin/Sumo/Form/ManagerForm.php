<?php


namespace Martin\Sumo\Form;


use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\Player;

class ManagerForm extends MenuForm
{
    public function __construct()
    {
        parent::__construct("Sumo - Game Manager", "Here you can control features of your custom sumo tournament!", [
            new MenuOption("Start the game"),
            new MenuOption("Close the game")
        ], function (Player $player, int $selected): void {
            if ($selected === 0) {
                $player->chat("/sumo start");
            }

            if ($selected === 1) {
                $player->chat("/sumo close");
            }
        });
    }
}