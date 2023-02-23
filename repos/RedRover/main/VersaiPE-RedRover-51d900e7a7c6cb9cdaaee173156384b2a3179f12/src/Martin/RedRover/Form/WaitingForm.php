<?php


namespace Martin\RedRover\Form;


use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\Player;

class WaitingForm extends MenuForm
{
    public function __construct()
    {
        parent::__construct("Waiting - RedRover", "",
            [
                new MenuOption("Select a team"),
                new MenuOption("Leave the event"),
            ],
            function (Player $player, int $selected): void {
                if ($selected === 0) {
                    TeamForm::createForm($player);
                }

                if ($selected === 1) {
                    $player->getServer()->getCommandMap()->dispatch($player, "/redrover leave");
                }
            });
    }
}