<?php
declare(strict_types=1);

namespace Duo\vcosmetics\session;

use pocketmine\player\Player;
use Duo\vcosmetics\Main;

class SessionManager {

    private Main $plugin;

    /** @var CosmeticSession[] */
    private array $sessions = [];

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function registerSession(Player $player){
        $this->sessions[$player->getName()] = new CosmeticSession($player);
    }

    public function unregisterSession(Player $player){
        unset($this->sessions[$player->getName()]);
    }

    public function getSession(Player $player): ?CosmeticSession{
        return $this->sessions[$player->getName()] ?? null;
    }
}