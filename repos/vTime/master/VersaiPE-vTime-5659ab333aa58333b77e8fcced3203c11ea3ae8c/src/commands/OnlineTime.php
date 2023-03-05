<?php

namespace Versai\vTime\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\vTime\data\DatabaseContext;
use Versai\vTime\Main;

class OnlineTime extends Command{

	private DatabaseContext $database;
	private Main $plugin;

    /**
     * OnlineTime constructor.
     *
     * @param DatabaseContext $database
     * @param Main $plugin
     */
	public function __construct(DatabaseContext $database, Main $plugin){
		$this->database = $database;
		$this->plugin = $plugin;
		parent::__construct("vtime", "View your or someone else's online time", "/vt total|session|top <player>", ["vt", "ot", "onlinetime"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (count($args) == 0) $sender->sendMessage($this->getUsage());
        else if (count($args) == 1 || strtolower($args[1]) == strtolower($sender->getName())) {
            if ($sender instanceof Player) {
                $username = $sender->getName();
                switch (strtolower($args[0])) {
                    case "reset":
                        if ($sender->hasPermission("vtime.command.reset")) {
                            $this->database->deleteAll(function() use ($sender): void{
                                $players = $this->plugin->getServer()->getOnlinePlayers();
                                foreach ($players as $player) {
                                    $this->database->onJoin($player->getName());
                                }
                                $sender->sendMessage(TextFormat::GREEN . "Online time reset successfully.");
                            });
                        }
                        break;
                    case "top":
                        $this->database->getTop(function ($rows) use ($sender) {
                            $message = TextFormat::GOLD . "--- Top 10 Most Active Players ---\n";
                            $i = 1;
                            foreach ($rows as $row) {
                                $time = $row["time"];
                                $output = sprintf('§b%02d §9hrs §b%02d §9mins §b%02d §9secs', ($time / 3600), ($time / 60 % 60), $time % 60);
                                $message .= TextFormat::BOLD . TextFormat::BLUE . $i . ". " . TextFormat::RESET . TextFormat::YELLOW . $row["username"] . " " . $output . TextFormat::EOL;
                                $i++;
                            }
                            $sender->sendMessage($message);
                        });
                        break;
                    case "session":
                        $this->database->getInfo($username, function ($info) use ($sender) {
                            if ($info == null) {
                                $sender->sendMessage(TextFormat::RED . "Player does not exist!");
                            } else {
                                $time = intval(microtime(true)) - $info["current"];
                                $output = sprintf('§b%02d §9hrs §b%02d §9mins §b%02d §9secs', ($time / 3600), ($time / 60 % 60), $time % 60);
                                $sender->sendMessage("§9Your session online time is: " . $output);
                            }
                        });
                        break;
                    default:
                        $this->database->getInfo($username, function ($info) use ($sender) {
                            if ($info == null) {
                                $sender->sendMessage(TextFormat::RED . "Player does not exist!");
                            } else {
                                $time = intval(microtime(true)) - $info["current"];
                                $time = $time + $info["time"];
                                $output = sprintf('§b%02d §9hrs §b%02d §9mins §b%02d §9secs', ($time / 3600), ($time / 60 % 60), $time % 60);
                                $sender->sendMessage("§9Your total online time is: " . $output);
                            }
                        });
                        break;
                }
            }
        } else {
            $username = $args[1];
            switch (strtolower($args[0])) {
                case "session":
                    $pl = $this->plugin->getServer()->getPlayerByPrefix($username);
                    if ($pl !== null && $pl->isConnected()) {
                        $username = $pl->getName();
                    }
                    $this->database->getInfo($username, function ($info) use ($username, $sender) {
                        if ($info === null) {
                            $sender->sendMessage(TextFormat::RED . "Player does not exist!");
                        } else if ($this->plugin->getServer()->getPlayerByPrefix($username) !== null) {
                            $time = (int)microtime(true) - $info["current"];
                            $output = sprintf('§b%02d §9hrs §b%02d §9mins §b%02d §9secs', ($time / 3600), ($time / 60 % 60), $time % 60);
                            $sender->sendMessage("§9" . $username . " session online time is: " . $output);
                        } else {
                            $sender->sendMessage("§cPlayer is offline!");
                        }
                    });
                    break;
                default:
                    $pl = $this->plugin->getServer()->getPlayerByPrefix($username);
                    if ($pl !== null && $pl->isConnected()) {
                        $username = $pl->getName();
                    }
                    $this->database->getInfo($username, function ($info) use ($username, $sender) {
                        if ($info === null) {
                            $sender->sendMessage(TextFormat::RED . "Player does not exist!");
                        } else if ($this->plugin->getServer()->getPlayerByPrefix($username) !== null) {
                            $time = (int)microtime(true) - $info["current"];
                            $time += $info["time"];
                            $output = sprintf('§b%02d §9hrs §b%02d §9mins §b%02d §9secs', ($time / 3600), ($time / 60 % 60), $time % 60);
                            $sender->sendMessage("§9" . $username . " total online time is: " . $output);
                        } else {
                            $time = $info["time"];
                            $output = sprintf('§b%02d §9hrs §b%02d §9mins §b%02d §9secs', ($time / 3600), ($time / 60 % 60), $time % 60);
                            $sender->sendMessage("§9" . $username . " total online time is: " . $output);
                        }
                    });
                    break;
            }
        }
    }
}
