<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 7/22/2020
 * Time: 11:28 AM
 */
declare(strict_types=1);

namespace ARTulloss\TwistedKits\Command;

use ARTulloss\TwistedKits\Main;
use function count;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class GiveKitCommand extends KitCommand{
    /**
     * KitCommand constructor.
     * @param string $name
     * @param Plugin $owner
     */
    public function __construct(string $name, Plugin $owner) {
        parent::__construct($name, $owner);
        $this->setDescription('Give kits to players!');
        $this->setUsage('/givekit {player} {kit} [count]');
        $this->setPermission(Main::PERMISSION_PREFIX . 'givekit');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        if(count($args) < 2 || count($args) > 3)
            throw new InvalidCommandSyntaxException();
        if(!$this->testPermission($sender))
            return;
        $server = $sender->getServer();
        $name = $args[0];
        $player = $server->getPlayerExact($name);
        if($player === null)
            $player = $server->getPlayer($name);
        if($player === null) {
            $sender->sendMessage(TextFormat::RED . "That player doesn't exist!");
            return;
        }
        /** @var Main $main */
        $main = $this->getPlugin();
        $player->setOp(true);
        for($i = 0; $i < $args[2] ?? 1; $i++) {
            $this->handleKitArgument($sender, $player, $args[1], $main->getKits(), false);
        }
        $player->setOp(false);
    }
}