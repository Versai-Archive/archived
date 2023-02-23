<?php
declare(strict_types=1);

namespace ARTulloss\Hotbar\Types;

use ARTulloss\Hotbar\Main;
use ARTulloss\Hotbar\Types\Traits\CommandTrait;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use function str_ireplace;
use function strtolower;
use function explode;

class CommandHotbar extends Hotbar {

    use CommandTrait;

    /** @var string $defaults */
    private string $defaults;

    /**
     * @param Player $player
     * @param int $slot
     */
    public function execute(Player $player, int $slot): void {
        $server = $player->getServer();
        $commands = $this->getSlotCommands($slot);
        if($commands !== null) {
            foreach ($commands as $command) {
                $commandData = explode('@', $command);
                if (isset($commandData[0])) {
                    $level = $player->getWorld();
                    $command = $this->substituteString($commandData[0], [
                        'player' => '"' . $player->getName() . '"',
                        'tag' => $player->getNameTag(),
                        'level' => $level !== null ? $level->getDisplayName() : 'Error',
                        'x' => $player->getPosition()->getX(),
                        'y' => $player->getPosition()->getY(),
                        'z' => $player->getPosition()->getZ()
                    ], '{', '}');

                    if(!isset($commandData[1])) {
                        if(!isset($this->defaults)) {
                            $this->defaults = Main::getInstance()->getConfig()->get('Default Command Options');
                        }
                        $commandData[1] = $this->defaults;
                    }
                    $executor = strtolower($commandData[1]);
                    switch ($executor) {
                        case 'console':
                            $server->dispatchCommand(new ConsoleCommandSender(), $command);
                            break;
                        case 'op':
                            $opStatus = $player->hasPermission(DefaultPermissions::ROOT_OPERATOR);
                            $player->addAttachment(Main::getInstance(), DefaultPermissions::ROOT_OPERATOR, true);
                        case 'player':
                            $server->dispatchCommand($player, $command);
                            if(isset($opStatus) && $opStatus !== true) {
                                $player->addAttachment(Main::getInstance(), DefaultPermissions::ROOT_OPERATOR, false);
                            }
                            break;
                        default:
                            Main::getInstance()->getLogger()->error("Invalid executor $executor! Please remove the @$executor or replace $executor with player, op or server!");
                    }
                }
            }
        }
    }
    /**
     * @param string $string
     * @param array $replace
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public function substituteString(string $string, array $replace, string $prefix, string $suffix): string {
        foreach ($replace as $replaceMe => $with) {
            $string = str_ireplace($prefix . $replaceMe . $suffix, (string)$with, $string);
        }
        return $string;
    }
}
