<?php declare(strict_types=1);

namespace vOT;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as C;
use vOT\command\OnlinetimeCommand;
use vOT\db\Database;

class Loader extends PluginBase {
    private ?Database $db;
    public static array $TIMES = [];

    public function onEnable(): void {
        $this->saveConfig();
        $this->getLogger()->info(C::GREEN . "Successfully enabled VersaiOT!");
        $this->getServer()->getCommandMap()->register("vot", new OnlinetimeCommand($this));
        // $this->db = new Database($this);
        // $this->db->init();
    }

    public function onDisable(): void {
        $this->db->close();
        $this->getLogger()->info(C::RED . "Successfully disabled VersaiOT!");
    }

    public function getDB(): ?Database {
        return $this->db;
    }
}