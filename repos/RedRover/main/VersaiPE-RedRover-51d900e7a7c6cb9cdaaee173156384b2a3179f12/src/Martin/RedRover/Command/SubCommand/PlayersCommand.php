<?php


namespace Martin\RedRover\Command\SubCommand;


use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use Martin\GameAPI\Command\BaseGameSubCommand;
use Martin\RedRover\Game\RedRover;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class PlayersCommand extends BaseGameSubCommand
{

    public function onRun(CommandSender $sender, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.player-only"));
            return;
        }

        $game = $this->getPlugin()->getGameByPlayer($sender);

        if ($game === null || !($game instanceof RedRover)) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.in-game"));
            return;
        }

        $options = [];
        foreach ($game->getRedTeam()->getPlayers() as $player) {
            $options[] = new MenuOption(TextFormat::RED . $player->getName());
        }

        foreach ($game->getBlueTeam()->getPlayers() as $player) {
            $options[] = new MenuOption(TextFormat::BLUE . $player->getName());
        }

        $sender->sendForm(new MenuForm("Player List", "", $options, static function (Player $player, int $selected): void {
        }));
    }

    protected function prepare(): void
    {
        // TODO: Implement prepare() method.
    }
}