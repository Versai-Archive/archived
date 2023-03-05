<?php
declare(strict_types=1);

namespace Versai\arenas;

use pocketmine\plugin\PluginBase;
use pocketmine\world\Position;
use Versai\arenas\commands\ArenaCommand;
use Versai\arenas\event\ArenaListener;

class Arenas extends PluginBase
{

    private static self $instance;

    public array $defaults = [], $arenas = [];
    public string $path;
    private $listener;

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public function onEnable(): void
    {

        $this->saveDefaultConfig();

        $this->listener = new ArenaListener($this);
        $this->getServer()->getPluginManager()->registerEvents($this->listener, $this);

        $this->path = $this->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR;
        if (!file_exists($this->path)) {
            mkdir($this->path);
        }

        $this->loadArenas();
        $this->getServer()->getCommandMap()->register("arenas", new ArenaCommand(
            $this,
            "arenas",
            "Arenas command!"
        ));
    }

    public function loadArenas(): void
    {
        $i = 0;
        foreach (scandir($this->path) as $arenaName) {
            if (isset(pathinfo($this->path . $arenaName)["extension"]) && pathinfo($this->path . $arenaName)["extension"] === "json") {
                $data = json_decode(file_get_contents($this->path . $arenaName), true);
                $spawnCoords = explode(":", $data["spawn"]);
                $spawnRadius = (int)($data["spawn-radius"] ?? 0);
                $name = str_replace(".json", "", $arenaName);
                $this->getServer()->getWorldManager()->loadWorld($name);
                $world = $this->getServer()->getWorldManager()->getWorldByName($name);
                if (count($spawnCoords) < 3 || !is_numeric($spawnCoords[0]) || !is_numeric($spawnCoords[1]) || !is_numeric($spawnCoords[2])) {
                    $this->getLogger()->error("Invalid spawn coordinates for arena $name, skipping");
                } else {
                    $this->arenas[$name] = new Arena(
                        $name,
                        $data["Kit-IDs"],
                        new Position((int)$spawnCoords[0], (int)$spawnCoords[1], (int)$spawnCoords[2], $world),
                        $data["settings"],
                        $spawnRadius
                    );
                }
                $i++;
            }
        }
        $this->getLogger()->notice($i === 0 ? "No arenas loaded" : "There are $i arena(s) loaded");
    }

    public function getListener()
    {
        if (isset($this->listener)) {
            return $this->listener;
        }
        return null;
    }

    public function registerArena(Arena $arena): void
    {
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

    public function getArenaByName(string $levelName): ?Arena
    {
        if (isset($this->arenas[$levelName])) {
            return $this->arenas[$levelName];
        }
        return null;
    }

    public function saveArena(Arena $arena): void
    {
        file_put_contents($this->path . $arena->getName() . ".json", json_encode($arena->getAll(), JSON_PRETTY_PRINT));
    }
}