<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Utilities;

use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\network\mcpe\protocol\types\InputMode;
use Versai\ModerationPM\Database\Container\Punishment;

class Utilities{

    public const DATE_TIME_REGEX = '/^(?:[0-9]+ )(?:seconds?|minutes?|hours?|days?|weeks?|months?|years?)$/i';
    public const DATE_TIME_REGEX_FAILED = "{length} violates length parameters! Must be a valid date time string";

    /**
     * @param int $until
     * @return bool
     */
    public static function isStillPunished(int $until): bool{
        $remaining = $until - time();
        if ($until === Punishment::FOREVER || $remaining > 0)
            return true;
        return false;
    }

    public static function translateDeviceOS(int $deviceOS): string{
        switch ($deviceOS) {
            case DeviceOS::ANDROID:
                return 'Android';
            case DeviceOS::IOS:
                return 'iOS';
            case DeviceOS::OSX:
                return 'Mac OS';
            case DeviceOS::AMAZON:
                return 'Fire OS';
            case DeviceOS::GEAR_VR:
                return 'Gear VR';
            case DeviceOS::HOLOLENS:
                return 'Hololens';
            case DeviceOS::WINDOWS_10;
                return 'Windows 10';
            case DeviceOS::WIN32;
                return 'Windows 32 bit';
            case DeviceOS::DEDICATED:
                return 'Dedicated';
            case DeviceOS::PLAYSTATION:
                return 'Playstation';
            case DeviceOS::NINTENDO:
                return 'Nintendo';
            case DeviceOS::XBOX:
                return 'Xbox';
            case DeviceOS::WINDOWS_PHONE:
                return 'Windows Phone';
        }
        return 'Unknown';
    }

    public static function translateInputMode(int $inputMode): string{
        switch ($inputMode) {
            case InputMode::MOUSE_KEYBOARD:
                return 'Mouse and Keyboard';
            case InputMode::TOUCHSCREEN:
                return 'Touch';
            case InputMode::GAME_PAD:
                return 'Game Pad';
            case InputMode::MOTION_CONTROLLER:
                return 'Controller';
        }
        return 'Unknown';
    }

    /**
     * @param string $string
     * @param string $front
     * @param string $back
     * @return string
     */
    public static function hash(string $string, string $front, string $back): string{
        return hash('sha256', $front . $string . $back);
    }
}
