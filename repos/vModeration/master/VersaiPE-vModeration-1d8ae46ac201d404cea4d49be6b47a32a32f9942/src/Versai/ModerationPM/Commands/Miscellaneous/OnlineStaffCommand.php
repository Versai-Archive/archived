<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\Miscellaneous;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Versai\ModerationPM\Commands\ModerationCommand;
use Versai\ModerationPM\Main;
use function count;
use function implode;

class OnlineStaffCommand extends ModerationCommand{

    protected function prepare(): void{
        $this->setPermission(Main::PERMISSION_PREFIX . 'onlinestaff');
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if ($this->testPermission($sender)){
            $staff = [];
            foreach ($sender->getServer()->getOnlinePlayers() as $player) {
                if ($player->hasPermission($this->getPermission())) {
                    $staff[] = $player->getName();
                }
            }
            $sender->sendMessage('There are ' . TextFormat::BLUE . count($staff) . TextFormat::WHITE . ' staff online!');
            if (count($staff) !== 0) {
                $sender->sendMessage('The staff are: ' . implode(', ', $staff));
            }
        }
    }
}
