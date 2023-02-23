<?php

namespace Versai\Provider;

use Medoo\Medoo;
use pocketmine\player\Player;

class Database {

    private Medoo $database;

    const PLAYER_DATA = "player_data";

    public function __construct(Medoo $database) {
        $this->database = $database;
    }

    public function getRawDatabase(): Medoo {
        return $this->database;
    }

    public function initTables(): void {
        $this->database->create(self::PLAYER_DATA, [
            "xuid" => [
                "VARCHAR(32)",
                "NOT NULL",
                "PRIMARY KEY"
            ],
            "username" => [
                "VARCHAR(64)",
                "NOT NULL"
            ],
            "kills" => [
                "INT",
                "NOT NULL",
                "DEFAULT 0"
            ]
        ]);
    }

    public function playerInDatabase(Player | string $player): bool {
        $xuid = $player;
        if ($player instanceof Player) {
            $xuid = $player->getXuid();
        }
        return $this->database->has(self::PLAYER_DATA, [
            "AND" => [
                "xuid" => $xuid
            ]
        ]);
    }

    public function playerInDatabaseByName(string $player): bool {
        return $this->database->has(self::PLAYER_DATA, [
            "AND" => [
                "username" => $player
            ]
        ]);
    }

    public function getPlayerData(string $xuid) {
        return $this->database->select(self::PLAYER_DATA, "*", [
            "xuid" => $xuid
        ]);
    }

    public function updatePlayer(PlayerSession $session) {
        if ($this->playerInDatabase($session->getPlayer())) {
            $this->database->update(self::PLAYER_DATA, [
                "kills" => $session->getKills(),
            ]);
            return;
        }

        $this->database->insert(self::PLAYER_DATA, [
            "xuid" => $session->getPlayer()->getXuid(),
            "username" => $session->getPlayer()->getName(),
            "kills" => $session->getKills()
        ]);
    }

}