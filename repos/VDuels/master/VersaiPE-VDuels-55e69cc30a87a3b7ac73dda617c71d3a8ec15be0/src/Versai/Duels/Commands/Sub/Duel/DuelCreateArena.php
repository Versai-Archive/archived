<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Duel;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\DuelCommand;
use Versai\Duels\Commands\SubCommand;
use Versai\Duels\Duels;
use jojoe77777\FormAPI\CustomForm;
use Duo\kits\Kits;
use function count;
use function explode;
use function is_numeric;
use function str_replace;

class DuelCreateArena extends SubCommand {

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
                (new DuelCommand('duel', Kits::getInstance()))->execute(new ConsoleCommandSender(), '', ['reload']);
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