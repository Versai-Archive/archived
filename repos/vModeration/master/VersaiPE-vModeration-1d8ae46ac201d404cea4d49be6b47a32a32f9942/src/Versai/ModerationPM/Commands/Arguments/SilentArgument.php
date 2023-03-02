<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\Arguments;

use CortexPE\Commando\args\RawStringArgument;
use pocketmine\command\CommandSender;

class SilentArgument extends RawStringArgument{

    public function canParse(string $testString, CommandSender $sender): bool{
        $lower = strtolower($testString);
        return in_array($lower, ['-s', '-silent', 'true', 'false']);
    }

    public function getSpanLength(): int{
        return 1;
    }
}