<?php

declare(strict_types=1);

namespace Versai\RPGCore\Tasks;

use Versai\RPGCore\Main;

use pocketmine\scheduler\Task;
use Versai\RPGCore\Data\SQLDataStorer;


class SaveDataTask extends Task {
    private $plugin;

    public function __construct(Main $plugin) {
		  $this->plugin = $plugin;
    }

    public function onRun(): void {
        foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $session = $this->plugin->getSessionManager();
            $playerSession = $session->getSession($player);

            $data = new SQLDataStorer($this->plugin);

            $data->registerPlayer($player);

            $data->setPlayerData(
            //   $playerSession->getClass(),
            //   $playerSession->getMaxMana(),
            //   $playerSession->getDefense(),
            //   $playerSession->getAgility(),
            //   $playerSession->getCoins(),
            //   $playerSession->getQuestId(),
            //   $playerSession->getQuestProgress()
            $playerSession
            );
        }
    }
}