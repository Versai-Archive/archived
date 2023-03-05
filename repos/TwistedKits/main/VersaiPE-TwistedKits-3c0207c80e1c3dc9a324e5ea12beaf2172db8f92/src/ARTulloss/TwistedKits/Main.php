<?php

declare(strict_types=1);

namespace ARTulloss\TwistedKits;

use function array_slice;
use ARTulloss\TwistedKits\Command\GiveKitCommand;
use ARTulloss\TwistedKits\Command\KitCommand;
use ARTulloss\TwistedKits\Events\Listener;
use function count;
use const DIRECTORY_SEPARATOR;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function mkdir;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use function str_replace;

class Main extends PluginBase{
    /** @var Kit[] $kits */
    private $kits;
    /** @var int $equipMode */
    private $equipMode;

    public const PERMISSION_PREFIX = "twistedkits.";

	public function onEnable(): void{
	    $server = $this->getServer();
	    $server->getPluginManager()->registerEvents(new Listener($this), $this);
	    $server->getCommandMap()->registerAll('twistedkits', [
	        new KitCommand('kit', $this),
            new GiveKitCommand('givekit', $this)
        ]);
	    $configArray = $this->getConfig()->getAll();
	    $this->equipMode = $configArray['Kit Equipping Mode'];
	    if(!file_exists($this->getDataFolder() . 'cooldowns'))
	        mkdir($this->getDataFolder() . 'cooldowns');
	    $kitArray = $configArray['Kits'];
	    foreach ($kitArray as $kitName => $kitData) {
	        $filePath = $this->getDataFolder() . 'cooldowns' . DIRECTORY_SEPARATOR . $kitName . '.json';
            if(!file_exists($filePath))
                file_put_contents($filePath, '{}');
            $armor = [];
	        foreach ($kitData['armor'] ?? [] as $itemString) {
	            $armor[] = $this->parseItemString($itemString, $kitName);
            }
	        $items = [];
	        foreach ($kitData['items'] ?? [] as $itemString) {
	            $items[] = $this->parseItemString($itemString, $kitName);
            }
	        $effects = [];
	        foreach ($kitData['effects'] ?? [] as $effectString) {
	            $effects[] = $this->parseEffectString($effectString);
            }
            $this->kits[$kitName] = new Kit($kitName, $kitData['cooldown'] ?? $configArray['Default Cooldown'], $armor, $items, $effects, (array) json_decode(file_get_contents($filePath), true));
        }
	}
	public function onDisable(): void{
	    foreach ($this->getKits() as $kit) {
	        $kit->saveCooldownJSON();
	        $this->getLogger()->info("Saved {$kit->getName()}.json");
        }
    }

    /**
     * @return Kit[]
     */
	public function getKits(): array{
	    return $this->kits;
    }
    /**
     * @return int
     */
    public function getEquipMode(): int{
	    return $this->equipMode;
	}
    /**
     * @param string $itemString
     * @param string|null $kitName
     * @return Item
     */
	public function parseItemString(string $itemString, string $kitName = null): Item {
	    $itemArray = explode(':', $itemString);
	    $name = $itemArray[0];
	    $id = (int) $itemArray[1];
	    $meta = (int) $itemArray[2];
	    $count = (int) $itemArray[3];
	    $lore = $itemArray[4];
	    $item = ItemFactory::get($id, $meta, $count);
	    if($name !== '-')
	        $kitName !== null
                ? $item->setCustomName(str_replace('{kit}', $kitName, $name))
                : $item->setCustomName($name);
	    if($lore !== '-') {
	        $lore = explode('\n', $lore);
	        foreach ($lore as $index => $line) {
	            $lore[$index] = str_replace('{kit}', $kitName, $line);
            }
            $item->setLore($lore);
        }
	    $enchantArray = array_slice($itemArray, 5);
	    // Add enchants
        $i = false;
        foreach ($enchantArray as $key => $value) {
            if ($i) {
                $i = false;
                continue;
            }
            $enchantment = Enchantment::getEnchantmentByName($enchantArray[$key]);
            if (isset($enchantArray[++$key]))
                $item->addEnchantment(new EnchantmentInstance($enchantment, (int) $enchantArray[$key]));
            else {
                $this->getLogger()->error("There is an error in the configuration of the enchants of an item in a line containing $itemString.");
            }
            $i = true;
        }
        return $item;
    }
    /**
     * @param string $effectString
     * @return EffectInstance
     */
    public function parseEffectString(string $effectString): EffectInstance{
	    $effectArray = explode(':', $effectString);
	    if(count($effectArray) !== 4) {
	        $this->getLogger()->error("There is an error in the configuration of the effects of a kit in a line containing $effectString.");
        }
	    return new EffectInstance(Effect::getEffectByName($effectArray[0]), (int) $effectArray[1], (int) $effectArray[2], (bool) $effectArray[3]);
    }
}
