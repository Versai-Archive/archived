<?php


namespace Martin\RedRover\Form;


use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use Martin\RedRover\Main;
use pocketmine\Player;

class SpectateForm extends MenuForm
{
    public function __construct()
    {
        $playersOptions = [];

        foreach (($games = Main::getInstance()->getGames()) as $game) {
            $playersOptions[] = new MenuOption($game->getCreator()->getName() . " | " . $game->getMap()->getName());
        }

        parent::__construct("Spectate - RedRover", "", $playersOptions, function (Player $player, int $selected) use ($games): void {
            $game = $games[$selected];
            $player->chat("/redrover spectate " . $game->getCreator()->getName());
        });
    }
}