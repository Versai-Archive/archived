<?php

declare(strict_types=1);

namespace Versai\RPG\Libs\Translate;

use Versai\RPG\Main;

class Translator {

    /** @var string */
    public string $path;

    /** @var Main */
    private $plugin;

    /**
     * @param string $path path to the folder with languages
     */
    public function __construct(Main $plugin, string $path) {
        $this->plugin = $plugin;
        $this->path = $this->plugin->getDataFolder() . $path . "/";
    }

    /**
     * @param string $string
     * @param array $replacements
     * @param string $language
     * @return string
     */
    public function translate(string $string, array $replacements, string $language = "en_US"): string {
        $filePath = $this->path . $language . ".ini";

        $data = parse_ini_file($filePath);
        if (!$data) return "Error translating string - could not find file";

        $base = $data[$string] ?? $string;

        $base = str_replace('{PREFIX}', Main::PREFIX, $base);

        foreach($replacements as $key => $param) {
            $base = str_replace('{' . $key . '}', $param, $base);
        }

        return $base;
    }

}