<?php


namespace Martin\GameAPI\Utils;

use Exception;
use function random_int;
use function str_replace;

class StringUtils
{
    public static function replaceVars(string $string, array $variables): string
    {
        foreach ($variables as $key => $variable) {
            $variable = (string)$variable;
            $string = str_replace("{" . $key . "}", $variable, $string);
        }

        return $string;
    }

    public static function generateCode(int $len = 6): string
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
        $code = "";

        if ($len === 0) {
            throw new Exception("Can't use 0 as code length!");
        }

        for ($i = 0; $i < $len; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $code;
    }

    public static function startsWith(string $needle, string $haystack): bool
    {
        return strpos($needle, $haystack) === 0;
    }
}