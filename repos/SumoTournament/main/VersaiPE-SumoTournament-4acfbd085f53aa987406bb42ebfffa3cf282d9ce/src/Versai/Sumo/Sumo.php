<?php


namespace Versai\Sumo;


use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Versai\Sumo\Command\SumoTournamentCommand;
use Versai\Sumo\Listener\GameListener;
use Versai\Sumo\Listener\SumoListener;
use Versai\Sumo\Session\BuildingArena;
use Versai\Sumo\Session\CachedArena;
use Versai\Sumo\Session\Session;

class Sumo extends PluginBase
{
    /**
     * @var Session[]
     */
    public array $currentSessions = [];

    /**
     * @var CachedArena[]
     */
    public array $arenas = [];

    /**
     * @var BuildingArena[]
     */
    public array $buildArenas = [];

    private Config $messageConfig;

    public function onEnable()
    {
        $this->saveDefaultConfig();
        $this->saveResource("messages.yml");
        $this->messageConfig = new Config($this->getDataFolder() . "messages.yml");

        $this->getServer()->getCommandMap()->register("sumotournament", new SumoTournamentCommand($this));
        $this->getServer()->getPluginManager()->registerEvents(new GameListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SumoListener(), $this);
        $this->loadUpArenas();
    }

    public function loadUpArenas() {
        foreach ($this->getConfig()->getNested("maps") as $map => $data) {
            $loadingArena = $this->loadArena($map, $data["name"], $data["joining-position"], $data["position1"], $data["position2"]);
            var_dump($data);
            if (!$loadingArena && $this->getConfig()->get("loadup-messages") === true) $this->getLogger()->alert("Couldn't load up arena " . $data["name"] ?? "Name Not Available");
            else if ($this->getConfig()->get("loadup-messages") === true) $this->getLogger()->info("Successfully loaded the arena " . $data["name"]);
        }

    }

    public function loadArena(string $levelName, string $arenaName, array $joiningPosition, array $playingPosition1, array $playingPosition2): bool {
        $level = $this->getServer()->getLevelByName($levelName);
        if (!$level) return false;
        if (!($this->hasEmptyXYZ($joiningPosition) || !$this->hasOnlyInteger($joiningPosition))) return false;
        if (!($this->hasEmptyXYZ($playingPosition1) || !$this->hasOnlyInteger($playingPosition1))) return false;
        if (!($this->hasEmptyXYZ($playingPosition2) || !$this->hasOnlyInteger($playingPosition2))) return false;
        $joiningPosition = new Vector3((int) $joiningPosition["x"], (int) $joiningPosition["y"], (int) $joiningPosition["z"]);
        $playingPosition1 = new Vector3((int) $playingPosition1["x"], (int) $playingPosition1["y"], (int) $playingPosition1["z"]);
        $playingPosition2 = new Vector3((int) $playingPosition2["x"], (int) $playingPosition2["y"], (int) $playingPosition2["z"]);
        $this->arenas[] = new CachedArena($level, $arenaName, $joiningPosition, $playingPosition1, $playingPosition2);
        return true;
    }

    private function hasEmptyXYZ(array $array): bool
    {
        return isset($array["x"]) && isset($array["y"]) && isset($array["z"]);
    }

    private function hasOnlyInteger(array $array, int $minIntegerCount = 3): bool
    {
        $f = array_filter($array, function ($v) {
            return is_numeric($v) || is_float($v);
        });
        return sizeof($f) >= $minIntegerCount;
    }

    public function closeSession(Player $player): bool
    {
        if (empty($this->currentSessions[$player->getName()])) return false;
        $session = $this->currentSessions[$player->getName()];
        $session->sendMessage($this->getMessageConfig()->get("tournament-closed", "The sumo tournament has been closed"));
        unset($this->currentSessions[$player->getName()]);
        return true;
    }

    public function getSessionByPlayer(Player $player): ?Session {
        foreach ($this->currentSessions as $session) {
            if (in_array($player->getName(), $session->players)) return $session;
        }

        return null;
    }

    /**
     * @param Session $session
     * @return array
     * @description Could've used yield but what if its empty
     */
    public function getFightingPlayers(Session $session): array {
        $players = [];
        foreach ($session->getWaitingPlayers() as $player => $state) {
            if ($state === Session::PLAYER_STATE_PLAYING) $players[] = $player;
        }
        return $players;
    }

    public function isBuildingArena(Player $player): bool
    {
        return isset($this->buildArenas[$player->getName()]);
    }

    /**
     * @return Config
     */
    public function getMessageConfig(): Config
    {
        return $this->messageConfig;
    }

    public function getMessage(string $key, array $vars = []): string {
        $message = $this->getMessageConfig()->get($key, "null");
        if ($message === "null") return TextFormat::RED. "Could not find message with key {$key}. Please contact the server administrator";

        foreach ($vars as $k => $v) {
            $message = str_replace($message, "{" . $k . "}", $v);
        }

        return $this->getPrefix() . TextFormat::RESET . $message;
    }

    public function getPrefix(): string {
        if ($this->getConfig()->get("prefix-usage")) return $this->getConfig()->get("prefix") . " ";
        else return "";
    }
}