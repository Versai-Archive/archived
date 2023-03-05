<?php
declare(strict_types=1);

namespace Versai\vTempRanks\commands;

use CortexPE\Hierarchy\Hierarchy;
use DateTime;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Versai\vTempRanks\commands\args\DateTimeArgument;
use Versai\vTempRanks\commands\args\ForeverArgument;
use Versai\vTempRanks\database\Provider;
use Versai\vTempRanks\libs\CortexPE\Commando\args\RawStringArgument;
use Versai\vTempRanks\libs\CortexPE\Commando\BaseCommand;
use Versai\vTempRanks\Main;
use Versai\vTempRanks\Utilities;
use function strtolower;

class TempRankCommand extends BaseCommand {

    private Provider $provider;
    private Main $plugin;

    public function __construct(Main $main, string $name, string $description = "", array $aliases = []){
        parent::__construct($main, $name, $description, $aliases);
        $this->plugin = $main;
        $this->provider = $main->getProvider();
    }

    public function prepare(): void{
        $this->setPermission("temprank.command");
        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new RawStringArgument("rank", false));
        $this->registerArgument(2, new DateTimeArgument("length", false));
        $this->registerArgument(2, new ForeverArgument("length", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if(!isset($args['name'])) return;
        if(!isset($args['rank'])) return;
        if(!isset($args['length'])) return;

        $name = $args['name'];
        $rank = $args['rank'];
        $until = $args['length'];

        if (strtolower($until) === 'forever'){
            $until = Utilities::FOREVER;
        }else{
            $until = (new DateTime("now + $until"))->getTimestamp();
        }

        /** @var Hierarchy $hierarchy */
        $hierarchy = $this->plugin->getServer()->getPluginManager()->getPlugin("vHierarchy");

        $member = $hierarchy->getMemberFactory()->getMember($name);
        $role = $hierarchy->getRoleManager()->getRoleByName($rank);

        if($role !== null){
            if(!$member->hasRole($role)){
                $member->addRole($role);
                $this->provider->asyncAddTempRank($name, $rank, $until);
                $sender->sendMessage(TextFormat::GREEN . "Temporarily given '{$role->getName()}' role to {$member->getName()}");
            } else {
                $sender->sendMessage(TextFormat::RED . "{$member->getName()} already has role '{$role->getName()}'");
            }
        }
    }
}