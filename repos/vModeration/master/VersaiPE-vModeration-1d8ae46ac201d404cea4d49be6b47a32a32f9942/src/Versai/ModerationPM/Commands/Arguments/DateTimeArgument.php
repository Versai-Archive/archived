<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\Arguments;

use CortexPE\Commando\args\RawStringArgument;
use pocketmine\command\CommandSender;
use Versai\ModerationPM\Utilities\Utilities;
use function preg_match;

class DateTimeArgument extends RawStringArgument{

    public function canParse(string $testString, CommandSender $sender): bool{
        return (bool)preg_match(Utilities::DATE_TIME_REGEX, $testString);
    }

    public function getSpanLength(): int{
        return 2;
    }
}
