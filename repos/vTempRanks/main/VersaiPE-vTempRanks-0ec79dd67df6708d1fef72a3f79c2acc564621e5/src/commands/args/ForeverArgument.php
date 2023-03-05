<?php
declare(strict_types=1);

namespace Versai\vTempRanks\commands\args;

use pocketmine\command\CommandSender;
use function strtolower;

class ForeverArgument extends DateTimeArgument{

    public function canParse(string $testString, CommandSender $sender): bool{
        return strtolower($testString) === 'forever';
    }

    public function getSpanLength(): int{
        return 1;
    }
}
