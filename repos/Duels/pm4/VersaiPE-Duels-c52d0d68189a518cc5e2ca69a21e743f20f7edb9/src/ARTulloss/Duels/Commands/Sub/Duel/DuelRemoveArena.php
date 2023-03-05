<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 3/1/2020
 * Time: 2:30 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands\Sub\Duel;

use ARTulloss\Duels\Commands\Constants;
use ARTulloss\Duels\Commands\DuelCommand;
use ARTulloss\Duels\Commands\SubCommand;
use ARTulloss\Duels\Duels;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;

use ARTulloss\Kits\Kits;
use function str_replace;

class DuelRemoveArena extends SubCommand
{
    /**
     * @param Player $sender
     * @param array $args
     */
    public function execute(Player $sender, array $args): void{
        /** @var Duels $duels */
        $duels = $this->command->getPlugin();
        $arenas = $duels->duelConfig['Arenas'];
        $levelName = $sender->getLevel()->getName();
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
        (new DuelCommand('duel', $this->command->getPlugin(), Kits::getInstance()))->execute(new ConsoleCommandSender(), '', ['reload']);
        $sender->sendMessage(str_replace(['{arena}', '{level}'], [$arenaName, $levelName], Constants::DUEL_REMOVE_ARENA_SUCCESS));
    }
}