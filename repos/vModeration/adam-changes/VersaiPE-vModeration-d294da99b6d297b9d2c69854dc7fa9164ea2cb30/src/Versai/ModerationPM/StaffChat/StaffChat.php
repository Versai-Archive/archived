<?php
declare(strict_types=1);

namespace Versai\ModerationPM\StaffChat;

use pocketmine\command\CommandSender;
use function str_replace;

class StaffChat{

    /** @var CommandSender[] $staff */
    private array $staff;
    private string $format;

    /**
     * StaffChat constructor.
     * @param string $format
     */
    public function __construct(string $format){
        $this->format = $format;
    }

    /**
     * @param CommandSender $player
     */
    public function addToStaffChat(CommandSender $player): void{
        $this->staff[$player->getName()] = $player;
    }

    /**
     * @param CommandSender $player
     * @return bool
     */
    public function isInStaffChat(CommandSender $player): bool{
        return isset($this->staff[$player->getName()]);
    }

    /**
     * @param CommandSender $player
     */
    public function removeFromStaffChat(CommandSender $player): void{
        unset($this->staff[$player->getName()]);
    }

    /**
     * @param CommandSender $sender
     * @param string $msg
     */
    public function sendMessage(CommandSender $sender, string $msg): void{
        $msg = str_replace(['{player}', '{msg}'], [$sender->getName(), $msg], $this->format);
        foreach ((array)$this->staff as $player) {
            $player->sendMessage($msg);
        }
    }
}
