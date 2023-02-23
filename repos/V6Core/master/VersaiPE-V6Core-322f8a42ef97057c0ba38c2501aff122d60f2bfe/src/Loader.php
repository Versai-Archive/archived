<?php
declare(strict_types=1);

namespace Versai\V6;

use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Versai\V6\arena\ArenaManager;
use Versai\V6\database\DatabaseManager;

class Loader extends PluginBase {

    use SingletonTrait;

    public ArenaManager $arenaManager;
    public DatabaseManager $databaseManager;

    public function onLoad(): void {
        self::setInstance($this);
    }

    public function onEnable(): void {
        $this->loadCommands();
        $this->loadListeners();
        $this->loadItems();
        $this->loadEntities();
        $this->loadManagers();
    }

    public function loadCommands(): void {
        $commands = [];
        $this->getServer()->getCommandMap()->registerAll("versai", $commands);
    }

    public function loadItems(): void {
        $items = [];

        foreach($items as $class) {
            ItemFactory::getInstance()->register(new $class(), true);
        }
    }

    public function loadEntities(): void {

    }

    public function loadListeners(): void {
        $listeners = [];

        foreach($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }
    }

    public function loadManagers(): void {
        $this->arenaManager = new ArenaManager($this);
        $this->databaseManager = new DatabaseManager($this);
    }
}