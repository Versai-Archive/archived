<?php


namespace Duels\commands;


use Duels\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;
use pocketmine\entity\Location;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class ArenaCommand extends Command
{

    private $template = ["spawns" => [
        "1" => "n.a",
        "2" => "n.a",
    ]];

    private Loader $own;

    /**
     * ArenaCommand constructor.
     * @param Loader $param
     */
    public function __construct(\Duels\Loader $param)
    {
        parent::__construct("duel");
        $this->own = $param;


    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (isset($args[0])) {

            switch ($args[0]) {

                case "create":

                    if (isset($args[1])) {
                        //create name mode
                       if (isset($args[2])) {
                       if ($sender instanceof Player) {
                           $cfg = new Config($this->own->getDataFolder()."/arenas/".$args[1].".yml", Config::YAML, [
                               "name" => $args[1],
                               "mode" => $args[2],
                               "divice-only" => false,
                               "spawns" => [
                                "1" => "n.a",
                                "2" => "n.a",
                               ],
                               "scoreboard" => [
                                   "title" => "  §l§bVERSAI   ",
                                   "lines" => [
                                       "Line 1",
                                       "Line 2",
                                       "Line 3",
                                   ]
                               ]
                           ]);

                           $sender->disconnect("§aArena created\nplease restart the server!", "restart the server");



                       }


                       }

                        } else {

                            $sender->sendMessage("§eUse: /duel create <name> <mode>");

                        }

                        break;

                case "setspawn":

                    #/duel setspawn <arena> <slot:1,2>

                    echo "Setspawn commaannd";

                    if (isset($args[1])) {

                        $arena = $args[1];

                        echo "Variable arena created";

                        if (isset($args[2])) {

                            echo "isset args 2";

                            if (is_numeric($args[2])) {


                                echo "é numerico";

                         $slot = $args[2];

                         if ($slot <= 2 && $slot != 0) {

                             echo 'é um ou 2';

                           if ($sender instanceof Player) {

                               echo "é player";

                               $slot = strval($args[2]);

                               $cfg = new Config($this->own->getDataFolder()."/arenas/".$args[1].".yml", Config::YAML);

                               #"1" => "0:0:0:0:0:world",

                               //$data = $cfg->getAll();

                               $this->template["spawns"][$slot] = $sender->getPosition()->getFloorX().":".$sender->getPosition()->getFloorY().":".$sender->getPosition()->getFloorZ().":".$sender->getLocation()->getYaw().":".$sender->getLocation()->getPitch().":".$sender->getWorld()->getDisplayName();

                               $sender->sendMessage("§eSpawn #".$args[2]." done!");

                               var_dump($this->template);

                               if ($this->template["spawns"]["1"] != 'n.a' && $this->template["spawns"]["2"] != 'n.a'){

                                   $cfg->set("spawns", $this->template["spawns"]);
                                   $cfg->save();

                                   //var_dump($new);

                                   $sender->sendMessage("§aAll done!");


                               }

                           }


                         }


                            }else {

                                $sender->sendMessage("Nao é numerico");

                            }



                        }


                    }


                    break;

                    }

            }


        }
}