<?php


namespace Martin\RedRover;


use Martin\GameAPI\Game\Maps\Map;
use Martin\GameAPI\GamePlugin;
use Martin\GameAPI\Kit\KitManager;
use Martin\GameAPI\Utils\StringUtils;
use Martin\RedRover\Command\RedRoverCommand;
use Martin\RedRover\Game\RedRover;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\Config;

class Main extends GamePlugin implements Listener
{
    public const END_TYPE_COMMAND = "command";
    public const ERROR_MESSAGE_KEY_NOT_FOUND = "§cThe message with the key {key} was not found!";

    private static Main $instance;

    private string $endType = "command";

    private string $endCommand = "hub";
    private Config $messageConfig;

    private array $messageCache = [];

    /**
     * @return Main
     */
    public static function getInstance(): Main
    {
        return self::$instance;
    }

    public function onEnable(): void
    {
        $this->registerListeners();
        $this->registerListener($this);
        $this->initConfig();
        $this->getServer()->getCommandMap()->register("redrover", new RedRoverCommand($this, "redrover", "RedRover Event", "", ["rr"]));
        self::$instance = $this;

        if (!KitManager::isInitalized()) {
            KitManager::init();
        }
    }

    private function initConfig(): void
    {
        $this->saveDefaultConfig();
        $this->saveResource("messages.yml");
        $this->messageConfig = new Config($this->getDataFolder() . "messages.yml");
        #$this->broadcasting = $this->getConfig()->getNested("broadcasting", true);
        $this->endType = $this->getConfig()->getNested("end-match.type", "command");
        $this->endCommand = $this->getConfig()->getNested("end-match.command", "hub");

        $this->initMaps();
    }

    private function initMaps(): void
    {
        foreach ($this->getConfig()->getNested("maps") as $data) {
            $this->initMap($data);
        }
    }

    public function initMap(array $data): void
    {
        $map = Map::fromJSON($data);
        if (is_null($map)) {
            $this->getLogger()->error("Could not loadup map " . $data["name"] ?? "Unknown");
            return;
        }

        if (in_array($map, $this->maps, true)) {
            return;
        }

        if ($map->getPositionCount() !== 3) {
            $this->getLogger()->alert("Could not loadup map {$map->getName()} because there are exactly 3 positions needed! {$map->getPositionCount()} positions given");
            return;
        }

        $this->maps[] = $map;
    }

    public function saveMap(array $data): void
    {
        $map = Map::fromJSON($data);
        if ($map === null) {
            return;
        }
        $this->maps[] = $map;
        $configData = $this->getConfig()->getAll();
        $configData["maps"][] = Map::parse($map);
        $this->getConfig()->setAll($configData);
        $this->getConfig()->save();
    }

    public function getMap(string $name): ?Map
    {
        foreach ($this->getMaps() as $map) {
            if (strtolower($name) === strtolower($map->getName())) {
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

    public function onQuit(PlayerQuitEvent $e): void
    {
        if ((($game = $this->getGameByPlayer($e->getPlayer())) !== null) && $game->getCreator() === $e->getPlayer()) {
            $newCreator = $game->getPlayers()[array_rand($game->getPlayers())];
            if ($game instanceof RedRover) {
                $game->setCreator($newCreator);
            }
        }
    }

    public function getMessage(string $key, array $vars = [], bool $prefix = true): string
    {
        if (isset($this->messageCache[$key])) {
            $message = $this->messageCache[$key];
            if ($message === self::ERROR_MESSAGE_KEY_NOT_FOUND) {
                return StringUtils::replaceVars($message, ["key" => $key]);
            }

            $message = StringUtils::replaceVars($message, $vars);
            return ($prefix ? $this->getPrefix() . " " : "") . $message;
        }

        $message = $this->getMessageConfig()->getNested($key, self::ERROR_MESSAGE_KEY_NOT_FOUND);
        if ($message === self::ERROR_MESSAGE_KEY_NOT_FOUND) {
            $this->messageCache[$key] = self::ERROR_MESSAGE_KEY_NOT_FOUND;
            return StringUtils::replaceVars($message, ["key" => $key]);
        }

        $this->messageCache[$key] = $message;
        $message = StringUtils::replaceVars($message, $vars);
        return ($prefix ? $this->getPrefix() . " " : "") . $message;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->getMessage("prefix", [], false);
    }

    /**
     * @return Config
     */
    public function getMessageConfig(): Config
    {
        return $this->messageConfig;
    }

    /**
     * @return string
     */
    public function getEndType(): string
    {
        return $this->endType;
    }

    /**
     * @return string
     */
    public function getEndCommand(): string
    {
        return $this->endCommand;
    }
}