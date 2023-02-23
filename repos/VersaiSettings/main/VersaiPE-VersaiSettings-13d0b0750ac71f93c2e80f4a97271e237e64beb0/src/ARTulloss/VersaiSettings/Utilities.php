<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/12/2020
 * Time: 4:31 PM
 */
declare(strict_types=1);

namespace ARTulloss\VersaiSettings;

use pocketmine\plugin\Plugin;
use pocketmine\Server;
use Closure;
use Throwable;

class Utilities{
    /**
     * @param Plugin|null $plugin
     * @return Closure
     */
    public static function getOnError(Plugin $plugin = null): Closure{
        return function (Throwable $error) use ($plugin): void{
            $hasLogger = $plugin !== null ? $plugin : Server::getInstance();
            $hasLogger->getLogger()->logException($error);
        };
    }
}