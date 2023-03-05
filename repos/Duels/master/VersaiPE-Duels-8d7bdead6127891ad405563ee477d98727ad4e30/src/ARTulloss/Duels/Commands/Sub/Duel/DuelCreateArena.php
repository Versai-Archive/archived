<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 3/1/2020
 * Time: 11:53 AM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands\Sub\Duel;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;

use ARTulloss\Duels\libs\jojoe77777\FormAPI\CustomForm;
use ARTulloss\Duels\Commands\Constants;
use ARTulloss\Duels\Commands\DuelCommand;
use ARTulloss\Duels\Commands\SubCommand;
use ARTulloss\Duels\Duels;
use ARTulloss\Kits\Kits;

use function str_replace;
use function count;
use function explode;
use function is_numeric;

class DuelCreateArena extends SubCommand
{
    /**
     * @param Player $sender
     * @param array $args
     */
    public function execute(Player $sender, array $args): void{
        $form = new CustomForm(function (Player $player, $data): void{
            if(isset($data)) {
                $arenaName = $data[0];
                if($arenaName === '') {
                    $player->sendMessage(Constants::DUEL_CREATE_ARENA_FAIL);
                    return;
                }
                $author = $data[1];
                $positions = $data[2];
                $positions = explode('|', $positions);
                $kitIds = explode(',', $data[3]);
                foreach ($positions as $coords) {
                    $coords = explode(':', $coords);
                    if(count($coords) !== 3) {
                        $player->sendMessage(Constants::DUEL_CREATE_ARENA_FAIL);
                        return;
                    }
                    foreach ($coords as $coord) {
                        if(!is_numeric($coord)) {
                            $player->sendMessage(Constants::DUEL_CREATE_ARENA_FAIL);
                            return;
                        }
                    }
                }
                foreach ($kitIds as $key => $kitId) {
                    if(!is_numeric($kitId)) {
                        $player->sendMessage(Constants::DUEL_CREATE_ARENA_FAIL);
                        return;
                    }
                    $kitIds[$key] = (int)$kitId;
                }
                /** @var Duels $duels */
                $duels = $this->command->getPlugin();
                $levelName = $player->getLevel()->getName();
                $arenaData = ['Level' => $levelName, 'Author' => $author, 'Positions' => $positions, 'Kit-IDs' => $kitIds];
                $duels->duelConfig['Arenas'][$arenaName] = $arenaData;
                $config = $duels->getConfig();
                $config->setAll($duels->duelConfig);
                $config->save();
                $player->sendMessage(str_replace(['{arena}', '{level}'], [$arenaName, $levelName], Constants::DUEL_CREATE_ARENA_SUCCESS));
                // Execute command to reload the duel arenas
                (new DuelCommand('duel', $this->command->getPlugin(), Kits::getInstance()))->execute(new ConsoleCommandSender(), '', ['reload']);
            }
        });
        $form->setTitle('Create Arena');
        $form->addInput('Map Name');
        $form->addInput('Author');
        $form->addInput('Positions', 'X:Y:Z|X:Y:Z|etc');
        $form->addInput('Kit IDs', '1,2,3,etc');
        $sender->sendForm($form);
    }
}