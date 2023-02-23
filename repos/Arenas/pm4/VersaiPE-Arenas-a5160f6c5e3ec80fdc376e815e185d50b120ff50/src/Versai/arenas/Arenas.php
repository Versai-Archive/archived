<?php
declare(strict_types=1);

namespace Versai\arenas;

use pocketmine\plugin\PluginBase;
use pocketmine\world\Position;
use Versai\arenas\blocks\BeeHive;
use Versai\arenas\commands\ArenaCommand;
use Versai\arenas\event\ArenaListener;

class Arenas extends PluginBase{

    public static Arenas $instance;
    public array $defaults = [], $arenas = [];
    public string $path;
    private $listener;

    public static function getInstance(): Arenas{
        return self::$instance;
    }

    public function onEnable(): void{
        self::$instance = $this;

        $this->saveDefaultConfig();

        $this->listener = new ArenaListener($this);
        $this->getServer()->getPluginManager()->registerEvents($this->listener, $this);

        $this->path = $this->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR;
        if(!file_exists($this->path)){
            mkdir($this->path);
        }

        $this->loadArenas();
        $this->getServer()->getCommandMap()->register("arenas", new ArenaCommand(
            $this,
            "arenas",
            "Arenas command!"
        ));
    }

    public function loadArenas(): void{
        $i = 0;
        foreach(scandir($this->path) as $arenaName){
            if (isset(pathinfo($this->path . $arenaName)["extension"]) && pathinfo($this->path . $arenaName)["extension"] === "json") {
                $data = json_decode(file_get_contents($this->path . $arenaName), true);
                $explosion = explode(":", $data["spawn"]);
                $name = str_replace(".json", "", $arenaName);
                $this->arenas[$name] = new Arena(
                    $name,
                    $data["Kit-IDs"],
                    new Position($explosion[0], $explosion[1], $explosion[2], null),
                    $data["settings"]
                );
                $i++;
            }
        }
        $this->getLogger()->notice($i === 0 ? "No arenas loaded" : "There are $i arena(s) loaded");
    }

    public function getListener(){
        if(isset($this->listener)){
            return $this->listener;
        }
        return null;
    }

    public function registerArena(Arena $arena): void{
        $this->arenas[$arena->getName()] = $arena;
    }

    public function unregisterArena(Arena $arena): bool
    {
        if (isset($this->arenas[$arenaName = $arena->getName()])) {
            unset($this->arenas[$arenaName]);
            return true;
        }
        return false;
    }

    public function getArenaByName(string $levelName): ?Arena{
        if(isset($this->arenas[$levelName])){
            return $this->arenas[$levelName];
        }
        return null;
    }

    public function saveArena(Arena $arena): void{
        file_put_contents($this->path . $arena->getName() . ".json", json_encode($arena->getAll(), JSON_PRETTY_PRINT));
    }
}