<?php

namespace Vote;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;

class Vote extends PluginBase implements Listener{

    /**
     * {Server api key} get at the website:
     * https://minecraftpocket-servers.com/servers/manage/
     *
     * Insert this key in the config.yml "at server.api.key:"
     *
     * @var string
     */
    public $key = "";
    
    public function onEnable(){
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->exec();

    }

    /**
     * This function allows record of
     * Config, Key and create the
     * folder.
     */
    public function exec(){
        $this->getLogger()->info(TextFormat::YELLOW."(Multi-serv) Working...");

        @mkdir($this->getDataFolder());
        
        $this->saveDefaultConfig();
        $this->getResource("config.yml");

        if($this->getConfig()->get("server.api.key") !== "Insert your Server API Key"){
            $this->key = $this->getConfig()->get("server.api.key");

        }else{
            $this->getLogger()->info(TextFormat::RED."Server api key is not valid or not edited, check in config.yml for activate it, plugin disabled.");
            $this->getServer()->getPluginManager()->disablePlugin($this->getServer()->getPluginManager()->getPlugin("Vote"));

        }

    }

    /**
     * This function allow launch the
     * processing of the vote system.
     *
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{

        switch($command->getName()){

            case "vote":  
                if($sender instanceof Player){
                    $sender->sendMessage($this->getConfig()->get("success.command.inspect"));

                    $this->InspectPlayer($sender);

                }else{
                    $sender->sendMessage($this->getConfig()->get("error.command.permission"));

                }

            break;  

        }

        return true;

    }

    /**
     * This function allows use the
     * inspection for unique the player.
     *
     * @param Player $player
     * @param String $type
     * @return bool
     */
    public function InspectPlayer(Player $player, string $type = "object"){
        $user = $player->getName();
        // attempted fix for players with spaces in their name
        $user = str_replace(" ", "%20", $user);

        $url0 = "https://minecraftpocket-servers.com/api/?";

        switch($type){
            case "object":
                $url1 = "object=votes&element=claim&key=".$this->key."&username=".$user;

                break;

            case "action":
                $url1 = "action=post&object=votes&element=claim&key=".$this->key."&username=".$user;

                break;

            default:
                return false;

                break;

        }

        $this->getServer()->getAsyncPool()->submitTask(new Query($user, $type, $url0.$url1));

        return true;

    }

    /**
     * This function allows use the
     * result of the query and make
     * operation to inform the
     * player of the result.
     *
     * @param String $user
     * @param String $type
     * @param Int $result
     * @return bool
     */
    public function Result(string $user, string $type, int $result){
        $user = str_replace("%20", " ", $user);
        $i = $this->getServer()->getPlayer($user);
        
        if($i){ 

            switch($type){
                case "object":

                    if($result === 0){
                        $i->sendMessage($this->getConfig()->get("error.object.unvoted&unclaimed")); //Player has not voted

                    }elseif($result === 1){
                        $i->sendMessage($this->getConfig()->get("success.object.voted&unclaimed")); //Player voted but not receive claim 

                        $this->InspectPlayer($i, "action");

                    }elseif($result === 2){
                        $i->sendMessage($this->getConfig()->get("error.object.voted&claimed")); //Player already voted and Claimed

                    }

                    break;

                case "action":

                    if($result === 0){
                        $i->sendMessage($this->getConfig()->get("error.action.voted&claimed")); //Player already voted and Claimed

                    }elseif($result === 1){
                        $i->sendMessage($this->getConfig()->get("success.action.voted&unclaimed")); //Player voted but not receive claim

                        $this->getServer()->broadcastMessage(TextFormat::WHITE.$user." ".$this->getConfig()->get("success.broadcast.message"));

                        $Action = new Action($this);
                        $Action->Player($i);

                    }
                
                    break;

            }

            return true;
           
        }

        return false;

    }

}


 

  