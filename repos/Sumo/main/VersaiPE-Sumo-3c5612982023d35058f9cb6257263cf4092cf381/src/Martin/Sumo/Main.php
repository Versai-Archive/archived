<?php

declare(strict_types=1);

namespace Martin\Sumo;

use Martin\GameAPI\Game\Game;
use Martin\GameAPI\Game\Maps\Map;
use Martin\GameAPI\GamePlugin;
use Martin\GameAPI\Kit\KitManager;
use Martin\GameAPI\Listener\GameWorldRuleListener;
use Martin\Sumo\Command\SumoCommand;
use Martin\Sumo\Game\SumoKit;
use Martin\Sumo\Listener\PvPListener;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Main extends GamePlugin implements Listener
{
    public const PREFIX = TextFormat::BLUE . "[Sumo] §r";

    private array $options = [];

    public function onEnable(): void
    {
        parent::onEnable();
        KitManager::init();
        KitManager::addKit(new SumoKit());
        $this->getServer()->getCommandMap()->register("sumo", new SumoCommand($this, "sumo", "Play Sumo Tournaments"));
        $this->registerListener($this);

        $this->registerListener(new GameWorldRuleListener($this));
        $this->registerListener(new PvPListener($this));

        $this->initConfig();

        $this->getGameRules()->setBuild(false);
        $this->getGameRules()->setBreak(false);
        $this->getGameRules()->setTakeHunger(false);
        $this->getGameRules()->setItemsDrop(false);
        $this->getGameRules()->setKeepInventory(false);
        $this->getGameRules()->setTakeFallDamage(false);
        $this->getGameRules()->setXpDrop(false);
    }

    private function initConfig(): void
    {
        $this->saveDefaultConfig();
        $this->options["onEnd"] = $this->getConfig()->get("ending.type", "command");
        $this->options["endCommand"] = $this->getConfig()->get("ending.command", "/hub");
        $this->initMaps();
    }

    private function initMaps(): void
    {
        foreach ($this->getConfig()->getNested("maps") ?? [] as $index => $mapData) {
            $this->initMap($mapData);
        }
    }

    public function initMap(array $data): void
    {
        if (isset($data["enabled"]) && $data["enabled"] === false) {
            return;
        }

        $mapData = Map::fromJSON($data);

        if (!$mapData) {
            return;
        }

        if (!$this->checkMapExist($mapData->getName())) {
            $this->maps[] = $mapData;
        }
    }

    public function checkMapExist(string $name): bool
    {
        return $this->getMap($name) !== null;
    }

    public function getMap(string $name): ?Map
    {
        foreach ($this->getMaps() as $map) {
            if (strtolower($map->getName()) === strtolower($name)) {
                return $map;
            }
        }

        return null;
    }

    /**
     * @return Map[]
     */
    public function getMaps(): array
    {
        return $this->maps;
    }

    public function hasMaps(): bool
    {
        return count($this->maps) >= 1;
    }

    public function queueGame(Game $game): Game
    {
        $game->setGameRules($this->getGameRules());
        $game->setGameSettings($this->getGameSettings());
        $this->games[] = $game;
        return $game;
    }

    public function getGameByPlayer(Player $player): ?Game
    {
        foreach ($this->games as $game) {
            foreach ($game->getPlayers(null, true) as $loopPlayer) {
                if ($loopPlayer === $player) {
                    return $game;
                }
            }
        }

        return null;
    }

    public function saveMap(Map $map): void
    {
        if ($this->checkMapExist($map->getName())) {
            return;
        }

        $configData = $this->getConfig()->getAll();
        $configData["maps"][] = Map::parse($map);
        $this->getConfig()->setAll($configData);
        $this->getConfig()->save();
        $this->initMap(Map::parse($map));
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
