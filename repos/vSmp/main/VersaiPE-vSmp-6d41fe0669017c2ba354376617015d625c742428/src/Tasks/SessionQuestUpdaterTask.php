<?php

declare(strict_types=1);

namespace Versai\RPGCore\Tasks;

use pocketmine\scheduler\Task;
use Versai\RPGCore\Main;
use Versai\RPGCore\Quests\Quest;

class SessionQuestUpdaterTask extends Task {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        foreach($this->plugin->getServer()->getonlinePlayers() as $player) {
            $sessionManager = $this->plugin->getSessionManager();
            $session = $sessionManager->getSession($player);

            if (!$session) {
                return;
            }

            $questId = $session->getQuestId();

            if(!$questId) {
                $session->setQuestId(1); //starts at 1 not 0
                return;
            }

            $quests = yaml_parse_file($this->plugin->getDataFolder() . "quests.yml");

            $quest = $quests[$questId]; //The data of the Id they are on

            if (!$session->getQuest()) {
                $session->setQuest(new Quest($session->getQuestId(), $quest["name"], $quest["visual"], $quest["description"], $quest["type"], $quest["difficulty"], $quest["requirement"], $quest["reqId"] ?? null));
            }

            if ($session->getQuestProgress() >= $session->getQuestRequired()) {
                $session->setQuestProgress(0);
                $session->nextQuest();

                $quest = $quests[$questId + 1];

                $session->setQuest(new Quest(($questId + 1), $quest["name"], $quest["visual"], $quest["description"], $quest["type"], $quest["difficulty"], $quest["requirement"], $quest["reqId"] ?? null));

                $player->sendTitle("§aQuest Complete", "Congratulations!");
            }
        }
    }

}