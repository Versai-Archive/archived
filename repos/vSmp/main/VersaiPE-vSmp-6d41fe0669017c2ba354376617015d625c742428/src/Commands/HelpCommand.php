<?php

declare(strict_types=1);

namespace Versai\RPGCore\Commands;

use Versai\RPGCore\Libraries\FormAPI\window\{
    SimpleWindowForm,
    CustomWindowForm
};
use Versai\RPGCore\Libraries\FormAPI\elements\Button;
use pocketmine\command\{
    Command,
    CommandSender
};
use pocketmine\plugin\{
	PluginOwned,
	PluginOwnedTrait
};
use pocketmine\player\Player;
use Versai\RPGCore\Main;

class HelpCommand extends Command implements PluginOwned {
    use PluginOwnedTrait;

    private Main $plugin;

    public function __construct($name, $description, Main $plugin) {
        parent::__construct($name, $description);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {

        $prefix = "§7[§2RPG§aCore§7]";

        // Finish all paths

        if ($sender->hasPermission("rpg") || $sender->hasPermission("rpg.command.help")) {
            $mainWindow = new SimpleWindowForm("SMP_MAIN_HELP", "§aSMP Help", "Get help for all of the things you need to know about the SMP", function(Player $player, Button $button) {
                switch($button->getText()) {
                    case "§9§lGeneral":
                        $mainGeneralHelp = new SimpleWindowForm("SMP_GENERAL_HELP", "§l§9General Help", "Help for all of the general things about the SMP", function(Player $player, Button $button) {
                            switch($button->getText()) {
                                case "§1Mana":
                                    $mainGeneralManaHelp = new CustomWindowForm("SMP_GENERAL_MANA_HELP", "§1Mana", "Help on how the mana system works");

                                    $mainGeneralManaHelp->addLabel("The mana system is made so that players can cast spells, increase there powers of being able to use mana. The mana system is used mainly to attack mobs, and it will regenerate over time. The amount of mana you max out at depends on what class you choose and how many upgrades you have");

                                    $mainGeneralManaHelp->showTo($player);
                                break;

                                case "§6Coins":
                                    $mainGeneralCoinHelp = new CustomWindowForm("SMP_GENERAL_COIN_HELP", "§6Coins", "Coins system help");

                                    $mainGeneralCoinHelp->addLabel("The Coins system in the SMP is what is used to purchase and sell things, using coins you can upgrade your armor, stats, and more. Getting coins is as simple as selling items to the corresponding NPC, the NPC will ask you if you want to shop, or sell, click sell then sell all the items that you can sell to that NPC for more money!");

                                    $mainGeneralCoinHelp->showTo($player);
                                break;
                            }
                        });
                        $mainGeneralHelp->addButton("mana", "§1Mana");
                        $mainGeneralHelp->addButton("defense", "§8Defense");
                        $mainGeneralHelp->addButton("quests", "§3Quests");
                        $mainGeneralHelp->addButton("coin", "§6Coins");
                        $mainGeneralHelp->showTo($player);
                    break;
                }
            });
            $mainWindow->addButton("general", "§9§lGeneral");
            $mainWindow->addButton("commands", "§4§lCommands");
            $mainWindow->addButton("mobs", "§a§lMobs");

            // TODO: ADD A DYNAMIC QUESTS HELP TO SEE EACH QUEST
            
            $mainWindow->showTo($sender);
        } else {
            return $sender->sendMessage("You do not have permission to use this command");
        }
    }
}
