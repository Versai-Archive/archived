<?php

declare(strict_types=1);

namespace Versai\RPG;

use pocketmine\player\Player;
use Versai\RPG\Libs\Translate\Translator;

class RPGPlayer extends Player {

    /** @var string */
    public $language = "en_US";

    /** @var int  */
    private int $coins;

    /**
     * Send a translated message using the config file
     *
     * @param string $string
     * @param array $replacements
     * @param string $lang
     * @return void
     */
    public function sendTranslated(string $string, array $replacements, string $lang) {
        $this->sendMessage(Main::getInstance()->getTranslator()->translate($string, $replacements, $lang));
    }

    public function setLanguage(string $language) {
        $this->language = $language;
    }

    public function getCustomLanguage() {
        return $this->language;
    }

    public function getCoins(): int {
        return $this->coins ?? 0;
    }

    public function setCoins(int $coins) {
        $this->coins = $coins;
    }

    public function addCoins(int $coins) {
        $this->coins += $coins;
    }

    public function removeCoins(int $coins) {
        $this->coins -= $coins;
    }

    public function canAfford(int $amount): bool {
        if ($this->coins >= $amount) return true;
        return false;
    }
}