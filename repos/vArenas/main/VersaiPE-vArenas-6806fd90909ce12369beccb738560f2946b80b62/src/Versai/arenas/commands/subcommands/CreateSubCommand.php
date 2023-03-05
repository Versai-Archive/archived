<?php

namespace Versai\arenas\commands\subcommands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\arenas\Arena;
use Versai\arenas\Arenas;
use Versai\arenas\Constants;
use Versai\arenas\libs\CortexPE\Commando\args\IntegerArgument;
use Versai\arenas\libs\CortexPE\Commando\args\RawStringArgument;
use Versai\arenas\libs\CortexPE\Commando\BaseSubCommand;
use Versai\arenas\libs\CortexPE\Commando\exception\ArgumentOrderException;

class CreateSubCommand extends BaseSubCommand implements Constants
{

    private Arenas $plugin;

    public function __construct(Arenas $plugin, string $name, string $description = "", array $aliases = [])
    {
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
        $this->usageMessage = "create <protection> <kitIDs>";
    }

    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void
    {
        $this->setPermission(self::CREATE_PERMISSION);
        $this->registerArgument(0, new IntegerArgument("protection", false));
        $this->registerArgument(1, new RawStringArgument("kit-ids", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $levelName = $sender->getWorld()->getDisplayName();
            if (isset($this->plugin->arenas[$levelName])) {
                $sender->sendMessage(TextFormat::RED . "An arena already exists for this world!");
            } else {
                if (isset($args["protection"])) {
                    $protection = (int)$args["protection"]; //Just in case, cast to int
                    $kitIDs = [];
                    if (isset($args["kit-ids"])) {
                        $explosion = explode(",", $args["kit-ids"]);
                        foreach ($explosion as $id) {
                            $kitIDs[] = (int)$id;
                        }
                    }
                    $defaults = $this->plugin->getConfig()->getAll()["Defaults"];
                    $defaults["Protection-Area"] = $protection;
                    $createdArena = new Arena(
                        $levelName,
                        $kitIDs,
                        $sender->getLocation(),
                        $defaults,
                        0
                    );
                    $this->plugin->saveArena($createdArena);
                    $sender->sendMessage(TextFormat::GREEN . "Successfully created arena: $levelName!");
                    $this->plugin->loadArenas();
                } else {
                    $sender->sendMessage(TextFormat::RED . "You did not provide enough arguments!");
                }
            }
        }
    }
}