<?php

namespace Versai\arenas\commands\subcommands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\arenas\Arenas;
use Versai\arenas\Constants;
use Versai\arenas\libs\CortexPE\Commando\args\RawStringArgument;
use Versai\arenas\libs\CortexPE\Commando\BaseSubCommand;
use Versai\arenas\libs\CortexPE\Commando\exception\ArgumentOrderException;

class SetSubCommand extends BaseSubCommand implements Constants
{

    private Arenas $plugin;

    public function __construct(Arenas $plugin, string $name, string $description = "", array $aliases = [])
    {
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
    }

    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void
    {
        $this->setPermission(self::SET_PERMISSION);
        $this->registerArgument(0, new RawStringArgument("setting", false));
        $this->registerArgument(1, new RawStringArgument("setting-value", false));
        $this->registerArgument(2, new RawStringArgument("arena", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            if (isset($args["setting"])) {
                if (isset($args["setting-value"])) {
                    if (isset($args["arena"])) {
                        if (isset($this->plugin->arenas[$args["arena"]])) {
                            $arena = $this->plugin->getArenaByName($args["arena"]);
                            $levelName = $args["arena"];
                            $setting = strtolower($args["setting"]);
                            $value = $args["setting-value"];
                            switch ($setting) {
                                case "spawn-radius":
                                    $arena->setSpawnRadius((int)$value);
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "spawn":
                                    $pos = $sender->getPosition()->asVector3();
                                    $arena->setSpawnLocation($pos);
                                    $x = (string)round($pos->getX());
                                    $y = (string)round($pos->getY());
                                    $z = (string)round($pos->getZ());
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $x, $y, $z in $levelName");
                                    break;
                                case "knockback":
                                case "kb":
                                    $arena->setKnockback((float)$value);
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "protection":
                                    $arena->setProtection((int)$value);
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "item-pickup":
                                case "pickup-items":
                                    $arena->setCanPickUpItems($this->toBool($value));
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "hunger-loss":
                                case "hunger":
                                    $arena->setHungerLoss($this->toBool($value));
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "fall-damage":
                                case "fall":
                                    $arena->setFallDamage($this->toBool($value));
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "placeable":
                                case "place":
                                    $arena->setPlaceable($this->toBool($value));
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "breakable":
                                case "break":
                                    $arena->setBreakable($this->toBool($value));
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "block-decay":
                                    $arena->setBlockDecay($this->toBool($value));
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "build-limit":
                                case "build-height":
                                    $arena->setBuildLimit((int)$value);
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "block-list":
                                    $explosion = explode(",", $value);
                                    $blockIDs = [];
                                    foreach ($explosion as $id) {
                                        $blockIDs[] = (int)$id;
                                    }
                                    $arena->setAllowedBlocksList($blockIDs);
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                            }
                        }
                    } else {
                        $levelName = $sender->getWorld()->getDisplayName();
                        if (isset($this->plugin->arenas[$levelName])) {
                            $arena = $this->plugin->getArenaByName($levelName);
                            $setting = strtolower($args["setting"]);
                            $value = $args["setting-value"];
                            switch ($setting) {
                                case "spawn-radius":
                                    $arena->setSpawnRadius((int)$value);
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "spawn":
                                    $pos = $sender->getPosition()->asVector3();
                                    $arena->setSpawnLocation($pos);
                                    $x = (string)round($pos->getX());
                                    $y = (string)round($pos->getY());
                                    $z = (string)round($pos->getZ());
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $x, $y, $z in $levelName");
                                    break;
                                case "knockback":
                                case "kb":
                                    $arena->setKnockback((float)$value);
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "protection":
                                    $arena->setProtection((int)$value);
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "item-pickup":
                                case "pickup-items":
                                    $arena->setCanPickUpItems($this->toBool($value));
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "hunger-loss":
                                case "hunger":
                                    $arena->setHungerLoss($this->toBool($value));
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "fall-damage":
                                case "fall":
                                    $arena->setFallDamage($this->toBool($value));
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "placeable":
                                case "place":
                                    $arena->setPlaceable($this->toBool($value));
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "breakable":
                                case "break":
                                    $arena->setBreakable($this->toBool($value));
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "block-decay":
                                    $arena->setBlockDecay($this->toBool($value));
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "build-limit":
                                case "build-height":
                                    $arena->setBuildLimit((int)$value);
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                                case "block-list":
                                    $explosion = explode(",", $value);
                                    $blockIDs = [];
                                    foreach ($explosion as $id) {
                                        $blockIDs[] = (int)$id;
                                    }
                                    $arena->setAllowedBlocksList($blockIDs);
                                    $sender->sendMessage(TextFormat::GREEN . "Successfully set '$setting' to $value in $levelName");
                                    break;
                            }
                        } else {
                            $sender->sendMessage(TextFormat::RED . "You are not currently in a registered arena!");
                        }
                    }
                }
            }
        }
    }

    public function toBool(string $var): bool
    {
        switch (strtolower($var)) {
            case "1":
            case "true":
            case "on":
            case "yes":
            case "y":
                return true;
            default:
                return false;
        }
    }
}