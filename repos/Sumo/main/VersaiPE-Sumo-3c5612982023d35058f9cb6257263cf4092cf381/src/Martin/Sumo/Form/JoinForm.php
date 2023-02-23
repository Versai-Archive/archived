<?php


namespace Martin\Sumo\Form;


use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use Martin\GameAPI\Types\GameStateType;
use Martin\Sumo\Game\Sumo;
use Martin\Sumo\Main;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class JoinForm extends MenuForm
{
    public function __construct(Main $plugin)
    {
        $games = $plugin->getGames();
        $gameOptions = [];
        foreach ($games as $key => $game) {
            if (!$game instanceof Sumo || $game->isPrivate()) {
                unset($games[$key]);
                return;
            }

            $format = $game->getCurrentState() === GameStateType::STATE_WAITING ? TextFormat::BLUE : TextFormat::RED;
            $gameOptions[] = new MenuOption($format . $game->getMap()->getName() . " - " . $game->getCreator()->getName());
        }

        parent::__construct("Sumo - Join", "Side note: Blue games are currently queueing up and red ones are already starting and you will join as a spectator!", $gameOptions, function (Player $player, int $selected) use ($games): void {
            $game = $games[$selected];
            $player->chat("/sumo join {$game->getCreator()->getName()}");
        });
    }
}