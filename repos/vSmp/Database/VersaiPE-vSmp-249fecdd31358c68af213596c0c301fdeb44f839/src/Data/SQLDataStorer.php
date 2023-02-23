<?php

declare(strict_types=1);

namespace Versai\RPGCore\Data;

use pocketmine\player\Player;
use Versai\RPGCore\Data\BaseDataStorer;
use poggit\libasyncql\libasyncql;
use Versai\RPGCore\Main;
use Versai\RPGCore\Sessions\PlayerSession;

class SQLDataStorer extends BaseDataStorer {

    const INITALIZE_TABLE_PLAYERS = "rpgcore.init.table1";
    const INITALIZE_TABLE_PLAYERDATA = "rpgcore.init.table2";
    // Set the player data in the PlayerData table
    const START_PLAYER = "rpgcore.player.startplayerdata";
    // Set the player data in the Players table
    const START_PLAYERS = "rpgcore.player.startplayers";

    // will return all player data
    const GET_ALL_PLAYER_DATA = "rpgcore.player.getData";
    const GET_PLAYER_CLASS = "rpgcore.player.getClass";
    const GET_MAX_MANA = "rpgcore.player.getMaxMana";
    const GET_DEFENSE = "rpgcore.player.getDefense";
    const GET_AGILITY = "rpgcore.player.getAgility";
    const GET_COINS = "rpgcore.player.getCoins";
    CONST GET_QUEST = "rpgcore.player.getQuestId";

    private $plugin;

    private $database;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->database = $this->plugin->getDataConnector();
    }

    public function initTables(): void {
        $this->database->executeGeneric(SQLDataStorer::INITALIZE_TABLE_PLAYERS);
        $this->database->executeGeneric(SQLDataStorer::INITALIZE_TABLE_PLAYERDATA);
    }

    public function registerPlayer(Player $player): void {

        if (is_null($player->getXuid())) {
            var_dump("Player XUID is Null...");

            return;
        }

        if (is_null($player->getName())) {
            var_dump("Player name is Null...");

            return;
        }

        $this->database->executeChange(SQLDataStorer::START_PLAYERS, [
            "xuid" => $player->getXuid(),
            "username" => $player->getName()
        ]);
    }

    public function setPlayerData(PlayerSession $session): void {

        if(!$session) {
            var_dump("no session");
            return;
        }

        var_dump($session->getPlayer()->getName());
        var_dump($session->getClass() . " - Class");
        var_dump($session->getMaxMana() . " - MaxMana");
        var_dump($session->getDefense() . " - Defense");
        var_dump($session->getAgility() . " - Agility");
        var_dump($session->getCoins() . " - Coins");
        var_dump($session->getQuestId() . " - QuestId");
        var_dump($session->getQuestProgress() . " Quest Progress");

        if(is_null($session->getClass()) || is_null($session->getMaxMana()) || is_null($session->getDefense()) || is_null($session->getAgility()) || is_null($session->getCoins()) || is_null($session->getQuestId()) || is_null($session->getQuestProgress())) {
            
            var_dump("Something in the players session is returning null");

            return;

        }

        $this->database->executeChange(SQLDataStorer::START_PLAYER, [
            "username" => $session->getPlayer()->getName(),
            "class" => $session->getClass(),
            "MaxMana" => $session->getMaxMana(),
            "defense" => $session->getDefense(),
            "agility" => $session->getAgility(),
            "coins" => $session->getCoins(),
            "questid" => $session->getQuestId(),
            "questProgress" => $session->getQuestProgress()
        ]);
    }

    public function getAllPlayerData(Player $player): void {
        $this->database->executeSelect(SQLDataStorer::GET_ALL_PLAYER_DATA, [
            "username" => $player->getName()
        ]);
    }

    public function getClass(Player $player): void {
        $this->database->executeSelect(SQLDataStorer::GET_PLAYER_CLASS, [
            "username" => $player->getName()
        ]);
    }

    public function getMaxMana(Player $player): void {
        $this->database->executeSelect(SQLDataStorer::GET_MAX_MANA, [
            "username" => $player->getName()
        ]);
    }

    public function getDefense(Player $player): void {
        $this->database->executeSelect(SQLDataStorer::GET_DEFENSE, [
            "username" => $player->getName()
        ]);
    }

    public function getAgility(Player $player): void {
        $this->database->executeSelect(SQLDataStorer::GET_AGILITY, [
            "username" => $player->getName()
        ]);
    }

    public function getCoins(Player $player): void {
        $this->database->executeSelect(SQLDataStorer::GET_COINS, [
            "username" => $player->getName()
        ]);
    }

    public function getQuest(Player $player): void {
        $this->database->executeSelect(SQLDataStorer::GET_QUEST, [
            "username" => $player->getName()
        ]);
    }
}