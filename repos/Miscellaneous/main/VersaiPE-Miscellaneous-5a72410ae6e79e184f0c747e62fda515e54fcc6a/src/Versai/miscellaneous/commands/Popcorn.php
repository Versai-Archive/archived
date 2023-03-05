<?php
declare(strict_types=1);

namespace Versai\miscellaneous\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use Versai\miscellaneous\Constants;

class Popcorn extends Command {

    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        if(!$this->testPermission($sender, Constants::POPCORN)){
            $sender->sendMessage(Constants::NO_PERMISSION);
            return;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage(Constants::PLAYER_ONLY);
            return;
        }
        if(isset($args[0])){
            $player = Server::getInstance()->getPlayerByPrefix($args[0]);
            if($player === null){
                $sender->sendMessage(str_replace('{player}', $args[0], Constants::PLAYER_OFFLINE));
                return;
            }
            if(isset($args[1])) {
                if((int)$args[1] > 15){
                    $sender->sendMessage(Constants::MAX_POPCORN);
                    return;
                }
                $xyz = (int)$args[1];
                $bb = $player->getBoundingBox()->expandedCopy($xyz, $xyz, $xyz);
                $entities = $player->getWorld()->getNearbyEntities($bb, $player);
                foreach($entities as $entity){
                    if($entity instanceof Player){
                        $pos = $entity->getPosition();
                        $x = $player->getPosition()->subtract($pos->getX(), $pos->getY(), $pos->getZ())->normalize()->multiply(-1.35)->x;
                        $z = $player->getPosition()->subtract($pos->getX(), $pos->getY(), $pos->getZ())->normalize()->multiply(-1.35)->z;
                        $entity->setMotion(new Vector3($x, 0.75, $z));
                    }
                }
            }
        }
    }
}