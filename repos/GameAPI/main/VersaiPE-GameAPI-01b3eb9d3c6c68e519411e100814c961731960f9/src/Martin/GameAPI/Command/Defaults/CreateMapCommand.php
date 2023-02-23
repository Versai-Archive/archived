<?php


namespace Martin\GameAPI\Command\Defaults;


use Martin\GameAPI\Command\BaseGameSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class CreateMapCommand
 * @package Martin\GameAPI\Command\Defaults
 * @description You have to implement this command yourself but a lot is already done. Martin\GameAPI\Game\Maps\UnfinishedMap is made for this
 */
abstract class CreateMapCommand extends BaseGameSubCommand
{
    public const ACTIONS = [
        "help" => "Get available actions",
        "create" => "Create a map for events",
        "name" => "Change the map's name",
        "position" => "Push a position to the event",
        "author" => "Add the author the map (BUILDER)",
        "stop" => "Drop the current map",
        "save" => "Save a map"
    ];

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        if (($this->getPermission() !== null) && !$this->testPermissionSilent($sender)) {
            return;
        }

        $this->onRun($sender, $args);
    }

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function onRun(CommandSender $sender, array $args): void
    {
        if (isset($args[0])) {
            $action = strtolower(array_shift($args));
        } else {
            $action = "help";
        }

        switch ($action) {
            case "create":
            {
                $this->create($sender, $args);
                break;
            }

            case "name":
            {
                $this->name($sender, $args);
                break;
            }

            case "position":
            {
                $this->position($sender, $args);
                break;
            }

            case "author":
            {
                $this->author($sender, $args);
                break;
            }

            case "stop":
            {
                $this->stop($sender, $args);
                break;
            }

            case "save":
            {
                $this->save($sender, $args);
                break;
            }

            case "help":
            default:
            {
                $sender->sendMessage(TextFormat::DARK_BLUE . "> Help - Creator Tool");
                foreach (self::ACTIONS as $action => $help) {
                    $sender->sendMessage(TextFormat::BLUE . "/" . $this->getName() . " " . $action . " | " . $help);
                }
            }
        }
    }

    # SubCommands you will have to implement
    abstract protected function create(Player $sender, array $args);

    abstract protected function name(Player $sender, array $args);

    abstract protected function position(Player $sender, array $args);

    abstract protected function author(Player $sender, array $args);

    abstract protected function stop(Player $sender, array $args);

    abstract protected function save(Player $sender, array $args);
}