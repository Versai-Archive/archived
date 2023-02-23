<?php

namespace Bavfalcon9\StaffHUD\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use pocketmine\Player;
use pocketmine\Server;

class staff extends Command {
    private $pl;

    public function __construct($pl) {
        parent::__construct("staff");
        $this->pl = $pl;
        $this->description = "Enable | Disable | Hotbar";
        $this->usageMessage = "/staff | /staff <hotbar>";
        $this->setPermission("StaffHUD.staffmode");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
       if (!$sender->hasPermission('StaffHUD.staffmode') && !$sender->isOp()) {
           $sender->sendMessage(TF::RED . "You do not have permission to use this command.");
           return false;
       }

       $disable = ($this->pl->staffHUD->isEnabled($sender)) ? true : false;
       if (isset($args[0]) && ($args[0] === 'hotbar' || $args[0] === 'fix')) {
           if ($disable) {
               $this->pl->staffHUD->hotbar($sender); // fix hotbar
               $sender->sendMessage('§eResent staff hotbar.');
               return true;
           } else {
               $sender->sendMessage('§cYou are not in staff mode.');
               return true;
           }
       }
       if ($disable) {
           $sender->sendMessage('§cDisabled Staff Mode.');
           $this->pl->staffHUD->disable($sender);
           return true;
       } else {
           $sender->sendMessage('§aEnabled Staff Mode.');
           $this->pl->staffHUD->enable($sender);
           return true;  
       }
       return true;
    }
}
