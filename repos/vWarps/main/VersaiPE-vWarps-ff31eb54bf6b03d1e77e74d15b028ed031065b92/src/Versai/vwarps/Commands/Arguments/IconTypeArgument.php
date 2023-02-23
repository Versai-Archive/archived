<?php
declare(strict_types=1);

namespace Versai\vwarps\Commands\Arguments;

use CortexPE\Commando\args\RawStringArgument;
use dktapps\pmforms\FormIcon;
use pocketmine\command\CommandSender;
use function explode;
use function strtolower;

class IconTypeArgument extends RawStringArgument {

    public const DELIMITER = ',';

    /**
     * @param string $testString
     * @param CommandSender $sender
     * @return bool
     */
    public function canParse(string $testString, CommandSender $sender): bool {
        $image = explode(self::DELIMITER, $testString);
        $type = $image[0] ?? '';
        $data = $image[1] ?? '';
        $type = strtolower($type);
        if(($type === FormIcon::IMAGE_TYPE_PATH || $type === FormIcon::IMAGE_TYPE_URL) && $data !== '') {
            return true;
        }
        return false;
    }
}
