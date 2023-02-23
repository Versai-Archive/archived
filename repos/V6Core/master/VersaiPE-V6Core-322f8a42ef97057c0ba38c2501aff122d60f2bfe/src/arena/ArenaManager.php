<?php
declare(strict_types=1);

namespace Versai\V6\arena;

use pocketmine\world\Position;
use Versai\V6\Loader;
use function explode;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;
use function pathinfo;
use function scandir;
use function str_replace;

class ArenaManager {

    private array $arenas = [];
    private string $path;

    public function __construct(private Loader $loader){
        $this->path = $this->loader->getDataFolder() . 'arenas' . DIRECTORY_SEPARATOR;
        $this->loadArenas();
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

    public function loadArenas(): void{
        $i = 0;
        foreach(scandir($this->path) as $arenaName){
            if (isset(pathinfo($this->path . $arenaName)["extension"]) && pathinfo($this->path . $arenaName)["extension"] === "json") {
                $data = json_decode(file_get_contents($this->path . $arenaName), true);
                $explosion = explode(":", $data["Spawn"]);
                $name = str_replace(".json", "", $arenaName);
                $this->arenas[$name] = new Arena(
                    $name,
                    $data["Kit-IDs"],
                    new Position((int)$explosion[0], (int)$explosion[1], (int)$explosion[2], $this->loader->getServer()->getWorldManager()->getWorldByName($name)),
                    $data["Settings"]
                );
                $i++;
            }
        }
        $this->loader->getLogger()->notice($i === 0 ? "No arenas loaded" : "There are $i arena(s) loaded");
    }
}