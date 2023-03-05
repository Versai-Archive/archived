<?php

namespace Duels\mode;

use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;

class Mode
{
    private string $name;

    private array $data;

    private float $kb;

    private array $contents;

    public function __construct(string $name, array $data)
    {

        $this->name = $name;

        $this->data = $data;

        $this->kb = $data["kb"];

        $this->contents = $data["items"];

    }

    public function sendContents(Player $player)
    {

        $inv = $player->getInventory();

        $armor = $player->getArmorInventory();

        foreach ($this->getContents() as $content) {

            $contentMixed = explode(":", $this->contents);

            $item = ItemFactory::getInstance()->get($contentMixed[0], $contentMixed[1], $contentMixed[2]);

            if (count($contentMixed) > 3) {

            $enchantment = EnchantmentIdMap::getInstance()->fromId($contentMixed[3]);

                $item->addEnchantment(new EnchantmentInstance($enchantment, $contentMixed[4]));

            }

            if ($item instanceof Armor) {

                $armor->addItem($item);

            }else {

                $inv->addItem($item);

            }

        }
        
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getKb(): float
    {
        return $this->kb;
    }

    /**
     * @return array
     */
    public function getContents(): array
    {
        return $this->contents;
    }


}