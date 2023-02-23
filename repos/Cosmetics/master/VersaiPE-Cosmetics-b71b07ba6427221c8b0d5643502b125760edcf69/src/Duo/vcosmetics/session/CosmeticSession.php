<?php
declare(strict_types=1);

namespace Duo\vcosmetics\session;

use pocketmine\player\Player;
use Duo\vcosmetics\events\settings\FlightSetEvent;
use Duo\vcosmetics\events\settings\FollowParticleSetEvent;
use Duo\vcosmetics\events\settings\HitParticleSetEvent;
use Duo\vcosmetics\Main;

class CosmeticSession {

    private ?Player $player;
    private int $hitParticle = 0, $followParticle = 0;
    private int $cape = 0, $spawnFlight = 0, $tag = 0, $clanTag = 0;
    private string $customTag = "";

    public function __construct(Player $player){
        $this->player = $player;
    }

    public function setCape($cape){
        $this->cape = $cape;
    }
    public function getCape(): int{
        return $this->cape;
    }

    public function setHitParticle(int $hitParticle){
        $this->hitParticle = $hitParticle;
        (new HitParticleSetEvent($this->player, $hitParticle))->call();
    }
    public function getHitParticle(): int{
        return $this->hitParticle;
    }

    public function setFollowParticle(int $followParticle){
        $this->followParticle = $followParticle;
        (new FollowParticleSetEvent($this->player, $followParticle))->call();
    }
    public function getFollowParticle(): int{
        return $this->followParticle;
    }

    public function setSpawnFlight(bool $spawnFlight){
        $this->spawnFlight = (int)$spawnFlight;
        (new FlightSetEvent($this->player, $spawnFlight))->call();
    }
    public function getSpawnFlight(): bool{
        return (bool)$this->spawnFlight;
    }

    public function setTag(int $tag){
        $this->tag = $tag;
    }
    public function getTag(): int{
        return $this->tag;
    }

    public function setClanTag(int $tag){
        $this->clanTag = $tag;
    }
    public function getClanTag(): int{
        return $this->clanTag;
    }

    public function setCustomTag(string $tag){
        $this->customTag = $tag;
    }
    public function getCustomTag(): string{
        return $this->customTag;
    }

    public function update(): void{
        $provider = Main::getInstance()->getProvider();
        $provider->asyncUpdatePlayer($this->player, function(): void{});
    }

    public function setData(array $data){
        $this->cape = (int)$data["cape"];
        $this->hitParticle = (int)$data["hitParticle"];
        $this->followParticle = (int)$data["followParticle"];
        $this->spawnFlight = (int)$data["spawnFlight"];
        $this->tag = (int)$data["tag"];
        $this->clanTag = (int)$data["clanTag"];
        $this->customTag = (string)$data["customTag"];
    }
}