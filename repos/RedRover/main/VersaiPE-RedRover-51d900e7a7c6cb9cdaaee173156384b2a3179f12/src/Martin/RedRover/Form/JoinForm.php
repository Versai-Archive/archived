<?php


namespace Martin\RedRover\Form;


use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use dktapps\pmforms\ModalForm;
use Martin\GameAPI\Game\Game;
use Martin\GameAPI\Types\GameStateType;
use Martin\RedRover\Game\RedRover;
use Martin\RedRover\Main;
use pocketmine\Player;

class JoinForm extends MenuForm
{
    public function __construct()
    {
        parent::__construct("Join RedRover", "", [
            new MenuOption("Join public event"),
            new MenuOption("Join private event")
        ], function (Player $player, int $selected): void {
            if ($selected === 0) {
                $player->sendForm($this->getPublic());
            }

            if ($selected === 1) {
                $player->sendForm($this->getPrivateForm());
            }
        });
    }

    private function getPublic(): MenuForm
    {
        $arenas = array_filter(Main::getInstance()->getGames(), static function (Game $game) {
            return !$game->isPrivate();
        });

        $usernames = array_map(static function (Game $game): string {
            return $game->getCreator()->getName();
        }, $arenas);
        $usernamesForm = [];

        foreach ($usernames as $username) {
            $usernamesForm[] = new MenuOption($username);
        }

        return new MenuForm("Join public event", "", $usernamesForm,
            function (Player $player, int $selected) use ($arenas): void {
                $arena = $arenas[$selected];
                if ($arena->getCurrentState() === GameStateType::STATE_WAITING) {
                    $team = TeamForm::getTeamByForm($player);
                    if ($team === RedRover::TEAM_RED) {
                        $player->getServer()->getCommandMap()->dispatch($player, "/redrover join {$arena->getCreator()->getName()} red");
                    } else if ($team === RedRover::TEAM_BLUE) {
                        $player->getServer()->getCommandMap()->dispatch($player, "/redrover join {$arena->getCreator()->getName()} blue");
                    }
                } else {
                    new ModalForm("Join up spectator", "Oops! Seems like the event already started! Do you still want to spectate?",
                        function (Player $player, bool $selected) use ($arena): void {
                            if ($selected) {
                                $player->getServer()->getCommandMap()->dispatch($player, "/redrover spectate {$arena->getCreator()->getName()}");
                            }
                        }, "Yes", "No");
                }
            });
    }

    public function getPrivateForm(): CustomForm
    {
        return new CustomForm("Join private event", [
            new Input("code", "Enter the event code")
        ], function (Player $player, CustomFormResponse $response): void {
            $code = $response->getString("code");
            $player->chat("/redrover join $code");
        });
    }
}