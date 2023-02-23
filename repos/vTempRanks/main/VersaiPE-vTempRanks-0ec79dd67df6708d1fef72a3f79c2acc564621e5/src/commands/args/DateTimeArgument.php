<?php
declare(strict_types=1);

namespace Versai\vTempRanks\commands\args;

use pocketmine\command\CommandSender;
use Versai\vTempRanks\libs\CortexPE\Commando\args\RawStringArgument;
use Versai\vTempRanks\Utilities;
use function preg_match;

class DateTimeArgument extends RawStringArgument{

    public function canParse(string $testString, CommandSender $sender): bool{
        return (bool)preg_match(Utilities::DATE_TIME_REGEX, $testString);
    }

    public function getSpanLength(): int{
        return 2;
    }
}
