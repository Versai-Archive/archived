<?php

declare(strict_types=1);

namespace Versai\RPGCore\Tasks;

use pocketmine\item\ItemIds;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use Versai\RPGCore\Utils\Utils;
use pocketmine\utils\TextFormat as TF;

class UpdateLoreTask extends Task {

    private Player $player;

    public function __construct(Player $player) {
        $this->player = $player;
    }

    public function onRun(): void {
        $inv = $this->player->getInventory();

        if($inv === null){
            throw new CancelTaskException("Cancelled task");
        }
        $items = $inv->getContents();

        foreach($items as $slot => $item) {

            $tools = [
                ItemIds::WOODEN_PICKAXE,
                ItemIds::STONE_PICKAXE,
                ItemIds::IRON_PICKAXE,
                ItemIds::GOLDEN_PICKAXE,
                ItemIds::DIAMOND_PICKAXE,
                ItemIds::WOODEN_SHOVEL,
                ItemIds::STONE_SHOVEL,
                ItemIds::IRON_SHOVEL,
                ItemIds::GOLDEN_SHOVEL,
                ItemIds::DIAMOND_SHOVEL,
                ItemIds::WOODEN_AXE,
                ItemIds::STONE_AXE,
                ItemIds::IRON_AXE,
                ItemIds::GOLDEN_AXE,
                ItemIds::DIAMOND_AXE
            ];
            
            if (in_array($item->getId(), $tools)) {

                $nbt = $item->getNamedTag();
                $level = $nbt->getInt("level", 0);
                $req = $nbt->getInt("req", 500);
                $xp = $nbt->getInt("xp", 0);


                if(Utils::isLevelUp($item) === true) {
                    $nbt->setInt("level", ($level + 1));
                    $nbt->setInt("xp", 0);
                    $nbt->setInt("req", (int)round($req * 1.5));

                    $mappedValue = Utils::map($xp, 0, $req, 0, 10);
                    $greenBarCount = round($mappedValue);
                    $greenBars = str_repeat(TF::GREEN . "|", (int)$greenBarCount );
                    $redBars = str_repeat(TF::RED . "|", (int)(20 - $greenBarCount ));

                    $lore = ["Level: " . $level, $greenBars . $redBars];
                    $item->setLore($lore);

                    $this->player->sendMessage(TF::YELLOW . $item->getName() . TF::GREEN . " has reached level " . TF::YELLOW . $level);
                }

                $mappedValue = Utils::map($xp, 0, $req, 0, 10);

                $greenBarCount = round($mappedValue);
                $greenBars = str_repeat(TF::GREEN . "|", (int)$greenBarCount);
                $redBars = str_repeat(TF::RED . "|", (int)(20 - $greenBarCount));

                $lore = ["Level: " . $level, TF::RESET . TF::GRAY . "[" . $greenBars . $redBars . TF::GRAY . "]"];
                $item->setLore($lore);

                $inv->setItem($slot, $item); //Needs to be set
            }
        }
    }
}