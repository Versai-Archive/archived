<?php
declare(strict_types=1);

namespace ARTulloss\FormStatusCommand;

use ARTulloss\FormStatusCommand\libs\jojoe77777\FormAPI\CustomForm;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\utils\Process;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function number_format;
use function round;
use function microtime;
use function floor;

class StatusCommand extends BaseCommand {

    /**
     * StatusCommand constructor.
     * @param string $name
     * @param Translatable|string $description
     * @param Translatable|string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission('pocketmine.command.status');
        $this->setUsage('%pocketmine.command.status.usage');
        $this->setDescription('%pocketmine.command.status.description');
    }

    /**
     * @param Player $sender
     * @param array $args
     */
    public function parseCommand(Player $sender, array $args): void {
        $this->sendForm($sender);
    }

    /**
     * @param Player $sender
     * @param string|null $name
     */
    public function sendForm(Player $sender, string $name = null): void {
        $server = $sender->getServer();
        $form = new CustomForm(null);
        $form->setTitle('Status');
        $form->addLabel(TextFormat::GOLD . "MOTD: " . TextFormat::RED . $server->getMotd());
        $form->addLabel(TextFormat::GOLD . "API Version: " . TextFormat::RED . $server->getApiVersion());

        $time = microtime(true) - $server->getStartTime();
        $seconds = floor($time % 60);
        $minutes = null;
        $hours = null;
        $days = null;
        if ($time >= 60) {
            $minutes = floor(($time % 3600) / 60);
            if ($time >= 3600) {
                $hours = floor(($time % (3600 * 24)) / 3600);
                if ($time >= 3600 * 24) {
                    $days = floor($time / (3600 * 24));
                }
            }
        }
        $uptime = ($minutes !== null ?
                ($hours !== null ?
                    ($days !== null ?
                        "$days days "
                        : "") . "$hours hours "
                    : "") . "$minutes minutes "
                : "") . "$seconds seconds";

        $form->addLabel(TextFormat::GOLD . "Uptime: " . TextFormat::RED . $uptime);

        $form->addLabel(TextFormat::GOLD . "Players Online: " . TextFormat::RED . count($server->getOnlinePlayers()) . " | " . $server->getMaxPlayers());
        $form->addLabel(TextFormat::GOLD . "TPS: " . $server->getTicksPerSecond() . " [" . $server->getTickUsage() . "] | Average: " . $server->getTicksPerSecondAverage() . " [" . $server->getTickUsageAverage() . "]");

        $bandwidth = $server->getNetwork()->getBandwidthTracker();
        $form->addLabel(TextFormat::GOLD . "Up: " . TextFormat::RED . round($bandwidth->getSend()->getAverageBytes() / 1024, 2) . " kB/s | Down: " . round($bandwidth->getReceive()->getAverageBytes() / 1024, 2) . " kB/s");

        $mUsage = Process::getAdvancedMemoryUsage();
        $rUsage = Process::getRealMemoryUsage();

        $form->addLabel(TextFormat::GOLD . "Main thread memory: " . TextFormat::RED . number_format(round(($mUsage[0] / 1024) / 1024, 2)) . " MB.");
        $form->addLabel(TextFormat::GOLD . "Total memory: " . TextFormat::RED . number_format(round(($mUsage[1] / 1024) / 1024, 2)) . " MB.");
        $form->addLabel(TextFormat::GOLD . "Total virtual memory: " . TextFormat::RED . number_format(round(($mUsage[2] / 1024) / 1024, 2)) . " MB.");

        $form->addLabel(TextFormat::GOLD . "Heap memory: " . TextFormat::RED . number_format(round(($rUsage[0] / 1024) / 1024, 2)) . " MB.");
        $form->addLabel(TextFormat::GOLD . "Maximum memory (system): " . TextFormat::RED . number_format(round(($mUsage[2] / 1024) / 1024, 2)) . " MB.");

        $form->addLabel(TextFormat::GOLD . "Threads: " . Process::getThreadCount() . " | Cores: " . Utils::getCoreCount());
        $form->addLabel(TextFormat::GOLD . "OS: " . Utils::getOS());

        $levels = $server->getWorldManager()->getWorlds();

        $form->addLabel(TextFormat::GOLD . "Levels Loaded: " . count($levels));
        foreach($levels as $level) {
            $levelName = $level->getFolderName() !== $level->getDisplayName() ? " (" . $level->getDisplayName() . ")" : "";
            $timeColor = $level->getTickRateTime() > 40 ? TextFormat::RED : TextFormat::YELLOW;
            $form->addLabel(TextFormat::GOLD . "World \"{$level->getFolderName()}\"$levelName: " .
                TextFormat::RED . number_format(count($level->getLoadedChunks())) . TextFormat::GREEN . " chunks, " .
                TextFormat::RED . number_format(count($level->getEntities())) . TextFormat::GREEN . " entities. " .
                "Time $timeColor" . round($level->getTickRateTime(), 2) . "ms"
            );
        }

        $sender->sendForm($form);

        unset($form);

    }
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function handleConsole(CommandSender $sender, string $commandLabel, array $args): void {
        $rUsage = Process::getRealMemoryUsage();
        $mUsage = Process::getAdvancedMemoryUsage();
        $server = $sender->getServer();
        $sender->sendMessage(TextFormat::GREEN . "---- " . TextFormat::WHITE . "Server status" . TextFormat::GREEN . " ----");
        $time = microtime(true) - $server->getStartTime();
        $seconds = floor($time % 60);
        $minutes = null;
        $hours = null;
        $days = null;
        if ($time >= 60) {
            $minutes = floor(($time % 3600) / 60);
            if ($time >= 3600) {
                $hours = floor(($time % (3600 * 24)) / 3600);
                if ($time >= 3600 * 24) {
                    $days = floor($time / (3600 * 24));
                }
            }
        }
        $uptime = ($minutes !== null ?
                ($hours !== null ?
                    ($days !== null ?
                        "$days days "
                        : "") . "$hours hours "
                    : "") . "$minutes minutes "
                : "") . "$seconds seconds";
        $sender->sendMessage(TextFormat::GOLD . "Uptime: " . TextFormat::RED . $uptime);
        $tpsColor = TextFormat::GREEN;
        if ($server->getTicksPerSecond() < 17) {
            $tpsColor = TextFormat::GOLD;
        } elseif ($server->getTicksPerSecond() < 12) {
            $tpsColor = TextFormat::RED;
        }
        $sender->sendMessage(TextFormat::GOLD . "Current TPS: {$tpsColor}{$server->getTicksPerSecond()} ({$server->getTickUsage()}%)");
        $sender->sendMessage(TextFormat::GOLD . "Average TPS: {$tpsColor}{$server->getTicksPerSecondAverage()} ({$server->getTickUsageAverage()}%)");

        $bandwidth = $server->getNetwork()->getBandwidthTracker();
        $sender->sendMessage(TextFormat::GOLD . "Network upload: " . TextFormat::RED . round($bandwidth->getSend()->getAverageBytes() / 1024, 2) . " kB/s");
        $sender->sendMessage(TextFormat::GOLD . "Network download: " . TextFormat::RED . round($bandwidth->getReceive()->getAverageBytes() / 1024, 2) . " kB/s");

        $sender->sendMessage(TextFormat::GOLD . "Thread count: " . TextFormat::RED . Process::getThreadCount());

        $sender->sendMessage(TextFormat::GOLD . "Main thread memory: " . TextFormat::RED . number_format(round(($mUsage[0] / 1024) / 1024, 2)) . " MB.");
        $sender->sendMessage(TextFormat::GOLD . "Total memory: " . TextFormat::RED . number_format(round(($mUsage[1] / 1024) / 1024, 2)) . " MB.");
        $sender->sendMessage(TextFormat::GOLD . "Total virtual memory: " . TextFormat::RED . number_format(round(($mUsage[2] / 1024) / 1024, 2)) . " MB.");

        $sender->sendMessage(TextFormat::GOLD . "Heap memory: " . TextFormat::RED . number_format(round(($rUsage[0] / 1024) / 1024, 2)) . " MB.");
        $sender->sendMessage(TextFormat::GOLD . "Maximum memory (system): " . TextFormat::RED . number_format(round(($mUsage[2] / 1024) / 1024, 2)) . " MB.");

        $globalLimit = $server->getMemoryManager()->getGlobalMemoryLimit();
        if($globalLimit > 0) {
            $sender->sendMessage(TextFormat::GOLD . "Maximum memory (manager): " . TextFormat::RED . number_format(round($globalLimit, 2), 2) . " MB.");
        }

        foreach($server->getWorldManager()->getWorlds() as $level) {
            $levelName = $level->getFolderName() !== $level->getDisplayName() ? " (" . $level->getDisplayName() . ")" : "";
            $timeColor = $level->getTickRateTime() > 40 ? TextFormat::RED : TextFormat::YELLOW;
            $sender->sendMessage(TextFormat::GOLD . "World \"{$level->getFolderName()}\"$levelName: " .
                TextFormat::RED . number_format(count($level->getLoadedChunks())) . TextFormat::GREEN . " chunks, " .
                TextFormat::RED . number_format(count($level->getEntities())) . TextFormat::GREEN . " entities. " .
                "Time $timeColor" . round($level->getTickRateTime(), 2) . "ms"
            );
        }
    }
}