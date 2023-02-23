<?php


namespace Martin\RedRover\Form;


use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use Martin\RedRover\Game\RedRover;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TeamForm
{
    private static $selection = [];

    /**
     * @param Player $player
     * @deprecated Use TeamForm::getTeamByForm (Makes all no sense???)
     */
    public static function createForm(Player $player): void
    {
        self::getTeamByForm($player);
        return;

        unset(self::$selection[$player->getLowerCaseName()]);

        if ($team !== -1) {
            $team_string = "";

            if ($team === RedRover::TEAM_RED) {
                $team_string = "red";
            }

            if ($team === RedRover::TEAM_BLUE) {
                $team_string = "blue";
            }

            if ($team === RedRover::TEAM_SPECTATOR) {
                $team_string = "spectator";
            }

        }
    }


    public static function getTeamByForm(Player $player): int
    {
        $endSelected = -1;

        $player->sendForm(new MenuForm("Select a team", "", [
            new MenuOption(TextFormat::RED . "Team Red"),
            new MenuOption(TextFormat::BLUE . "Team Blue"),
            new MenuOption(TextFormat::GRAY . "Spectator")
        ], function (Player $player, int $selected) use (&$endSelected): void {
            $endSelected = $selected;

            if ($selected === 0) {
                $player->chat("/redrover team red");
            }

            if ($selected === 1) {
                $player->chat("/redrover team blue");
            }

            if ($selected === 2) {
                $player->chat("/redrover team spectator");
            }

        }));

        return $endSelected;
    }
}