<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Duel;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\DuelCommand;
use Versai\Duels\Commands\SubCommand;
use Versai\Duels\Duels;
use Duo\kits\Kits;
use function str_replace;

class DuelRemoveArena extends SubCommand {

    /**
     * @param Player $sender
     * @param array $args
     */
    public function execute(Player $sender, array $args): void{
        /** @var Duels $duels */
        $duels = Duels::getInstance();
        $arenas = $duels->duelConfig['Arenas'];
        $levelName = $sender->getWorld()->getDisplayName();
        $foundLevel = false;
        foreach ($arenas as $key => $arena) {
            if($arena['Level'] === $levelName) {
                $arenaName = $key;
                unset($arenas[$key]);
                $foundLevel = true;
            }
        }
        if(!$foundLevel) {
            $sender->sendMessage(str_replace('{level}', $levelName, Constants::DUEL_REMOVE_ARENA_FAIL));
            return;
        }
        $duels->duelConfig['Arenas'] = $arenas;
        $config = $duels->getConfig();
        $config->setAll($duels->duelConfig);
        $config->save();
        // Execute command to reload the duel arenas
        (new DuelCommand('duel', Kits::getInstance()))->execute(new ConsoleCommandSender(), '', ['reload']);
        $sender->sendMessage(str_replace(['{arena}', '{level}'], [$arenaName, $levelName], Constants::DUEL_REMOVE_ARENA_SUCCESS));
    }
}