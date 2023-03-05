<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/10/2020
 * Time: 1:33 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

use ARTulloss\Duels\Duels;
use ARTulloss\Kits\Kits;

class EloRestoreCommand extends PluginCommand{
    /**
     * EloRestoreCommand constructor.
     * @param string $name
     * @param Plugin $owner
     */
    public function __construct(string $name, Plugin $owner) {
        parent::__construct($name, $owner);
        $this->setDescription('Restore elo from previous system!');
    }
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        if($sender instanceof Player) {
            $sender->sendMessage(TextFormat::GREEN . 'Restoring your elo!');
            /** @var Duels $duels */
            $duels = $this->getPlugin();
            $eloManager = $duels->getEloManager();
            foreach (Kits::getInstance()->kitTypes as $kitType) {
                $config = new Config($duels->getDataFolder() . 'elo' . DIRECTORY_SEPARATOR . $kitType . '.json');
                $configArray = $config->getAll();
                $elo = $configArray[$sender->getName()] ?? null;
                unset($configArray[$sender->getName()]);
                $config->setAll($configArray);
                $config->save();
                if($elo !== null) {
                    $eloManager->setElo($kitType, $sender->getName(), $elo, null);
                    $sender->sendMessage(TextFormat::GREEN . 'Restored elo for ' . $kitType);
                } else
                    $sender->sendMessage(TextFormat::RED . 'Elo already restored for ' . $kitType);
            }
        } else
            $sender->sendMessage(TextFormat::RED . 'That command is only for players!');
    }
}