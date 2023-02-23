<?php

namespace Versai\vcps\data;

use pocketmine\player\Player;

class DataManager{

    private static self $instance;

    public static function init() : void{
        if(self::$instance !== null){
            // this has already been initialized
            return;
        }
        self::$instance = new self();
    }

    public static function getInstance() : ?self{
        return self::$instance;
    }

    private $dataList = [];

    public function add(Player $player) : void{
        $this->dataList[$player->getId()] = new ClickData();
    }

    public function get(Player $player) : ?ClickData{
        return $this->dataList[$player->getId()] ?? null;
    }

    public function remove(Player $player) : void{
        unset($this->dataList[$player->getId()]);
    }

}