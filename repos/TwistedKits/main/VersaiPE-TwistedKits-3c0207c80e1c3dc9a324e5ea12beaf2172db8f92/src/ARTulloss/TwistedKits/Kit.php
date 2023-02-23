<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 7/21/2020
 * Time: 10:04 PM
 */
declare(strict_types=1);

namespace ARTulloss\TwistedKits;

use ARTulloss\TwistedKits\Utilities\Utilities;
use function file_put_contents;
use function json_encode;
use const JSON_PRETTY_PRINT;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use function time;

class Kit {
    /** @var string $name */
    private $name;
    /** @var int $cooldown */
    private $cooldown;
    /** @var Armor[] $armor */
    private $armor;
    /** @var Item[] $items */
    private $items;
    /** @var EffectInstance $effects */
    private $effects;
    /** @var array $cooldowns */
    private $cooldowns;
    /**
     * Kit constructor.
     * @param string $name
     * @param int $cooldown
     * @param Armor[] $armor
     * @param Item[] $items
     * @param EffectInstance[] $effects
     * @param int[] $cooldowns
     */
    public function __construct(string $name, int $cooldown, array $armor, array $items, array $effects, array $cooldowns) {
        $this
            ->setName($name)
            ->setCooldown($cooldown)
            ->setArmor($armor)
            ->setItems($items)
            ->setEffects($effects)
            ->setCooldowns($cooldowns);
    }
    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): Kit{
        $this->name = $name;
        return $this;
    }
    /**
     * @return string
     */
    public function getName(): string{
        return $this->name;
    }
    /**
     * @param int $cooldown
     * @return Kit
     */
    public function setCooldown(int $cooldown): Kit{
        $this->cooldown = $cooldown;
        return $this;
    }
    /**
     * @return int
     */
    public function getCooldown(): int{
        return $this->cooldown;
    }
    /**
     * @param array $armor
     * @return Kit
     */
    public function setArmor(array $armor): Kit{
        $this->armor = $armor;
        return $this;
    }
    /**
     * @return Armor[]
     */
    public function getArmor(): array{
        return $this->armor;
    }
    /**
     * @param Item[] $items
     * @return Kit
     */
    public function setItems(array $items): Kit{
        $this->items = $items;
        return $this;
    }
    /**
     * @return Item[]
     */
    public function getItems(): array{
        $items = [];
        foreach ($this->items as $key => $item)
            $items[$key] = clone $item;
        return $items;
    }
    /**
     * @param array $effects
     * @return Kit
     */
    public function setEffects(array $effects): Kit{
        $this->effects = $effects;
        return $this;
    }
    /**
     * @return Item[]
     */
    public function getEffects(): array{
        $effects = [];
        foreach ($this->effects as $key => $effect)
            $effects[$key] = clone $effect;
        return $effects;
    }
    /**
     * @param array $cooldowns
     * @return Kit
     */
    public function setCooldowns(array $cooldowns): Kit{
        $this->cooldowns = $cooldowns;
        return $this;
    }
    /**
     * @return int[]
     */
    public function getCooldowns(): array{
        return $this->cooldowns;
    }
    /**
     * @param Player $player
     */
    public function updatePlayerLastUsedKit(Player $player): void{
        $this->cooldowns[$player->getName()] = time();
    }
    /**
     * @param Player $player
     * @return int
     */
    public function getPlayerCooldown(Player $player): int{
        return $this->cooldowns[$player->getName()] ?? 0;
    }
    /**
     * @param Player $player
     * @return bool
     */
    public function isPlayerOnCooldown(Player $player): bool{
        return time() - $this->getPlayerCooldown($player) <= $this->getCooldown();
    }
    /**
     * @param Player $player
     */
    public function sendToPlayer(Player $player): void{
        $inv = $player->getInventory();
        $armorInv = $player->getArmorInventory();
        /** @var Main $main */
        $main = $player->getServer()->getPluginManager()->getPlugin('TwistedKits');
        switch ($main->getEquipMode()) {
            case Modes::MODE_CLEAR_INVENTORY:
                $inv->setContents($this->getItems());
                $armorInv->setContents($this->getArmor());
                $player->removeAllEffects();
                foreach ($this->getEffects() as $effect)
                    $player->addEffect($effect);
                break;
            case Modes::MODE_ADD_ITEMS_CLEAR_EFFECTS:
                $player->removeAllEffects();
            case Modes::MODE_ADD_ITEMS:
                foreach ($this->getItems() as $item) {
                    if($inv->canAddItem($item))
                        $inv->addItem($item);
                    else
                        Utilities::createItemEntity($item, $player);
                }
                foreach ($this->getArmor() as $armor) {
                    if($armorInv->canAddItem($armor))
                        $armorInv->addItem($armor);
                    elseif($inv->canAddItem($armor))
                        $inv->addItem($armor);
                    else
                        Utilities::createItemEntity($armor, $player);
                }
                foreach ($this->getEffects() as $effect)
                    $player->addEffect($effect);
        }
    }
    public function saveCooldownJSON(): void{
        $main = Server::getInstance()->getPluginManager()->getPlugin('TwistedKits');
        $path = $main->getDataFolder() . 'cooldowns' . DIRECTORY_SEPARATOR . $this->getName() . '.json';
        foreach ($this->cooldowns as $playerName => $cooldown) {
            if(!(time() - $cooldown <= $this->getCooldown()))
                unset($this->cooldown[$playerName]);
        }
        file_put_contents($path, json_encode($this->cooldowns, JSON_PRETTY_PRINT));
    }
}