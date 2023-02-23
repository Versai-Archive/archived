<?php


namespace Martin\RedRover\Form;


use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use Martin\GameAPI\Game\Game;
use Martin\RedRover\Main;
use pocketmine\Player;

class ManageForm extends MenuForm
{
    public function __construct()
    {
        parent::__construct("Manage RedRover", "", [
            new MenuOption("Start the event"),
            new MenuOption("Switch teams"),
            new MenuOption("Kick a player out of the event"),
            new MenuOption("Close the event")
        ],
            function (Player $player, int $selected): void {
                switch ($selected) {
                    case 0:
                    {
                        $player->chat("/redrover start");
                        break;
                    }

                    case 1:
                    {
                        TeamForm::createForm($player);
                        break;
                    }

                    case 2:
                    {
                        $player->sendForm(self::kickForm(Main::getInstance()->getGameByPlayer($player)));
                    }

                    case 3:
                    {
                        $player->chat("/redrover close");
                        break;
                    }
                    default:
                        break;
                }
            });
    }

    public static function kickForm(Game $game): MenuForm
    {
        $players = $game->getPlayers();
        $mapped_players = array_map(static function (Player $player) {
            return new MenuOption($player->getName());
        }, $players);

        return new MenuForm("Kick a player", "Select the targetted player", $mapped_players, function (Player $player, int $submitted) use ($players): void {
            $player->chat("./redrover kick " . $players[$submitted]);
        });
    }
}