<?php


namespace Martin\GameAPI\Game\Position;

use Exception;
use Martin\GameAPI\Game\Game;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;

class GamePosition extends Vector3
{
    private ?float $yaw = null;

    private ?float $pitch = null;

    public function __construct(int $x = 0, int $y = 0, int $z = 0, ?int $yaw = null, ?int $pitch = null)
    {
        parent::__construct($x, $y, $z);
        $this->setYaw($yaw);
        $this->setPitch($pitch);
    }

    public static function teleport(Player $player, Game $game, self $position): void
    {
        if ($game->getLevel() === null) {
            throw new Exception("Level not loaded up in game");
        }

        $levelPos = Position::fromObject($position);
        $levelPos->setLevel($game->getLevel());

        $player->teleport($levelPos, $position->getYaw(), $position->getZ());
    }

    /**
     * @return float|null
     */
    public function getYaw(): ?float
    {
        return $this->yaw;
    }

    /**
     * @param float|null $yaw
     */
    public function setYaw(?float $yaw): void
    {
        $this->yaw = $yaw;
    }

    public static function fromPlayer(Player $player): self
    {
        $self = self::fromVector($player->asVector3());
        $self->setYaw($player->getYaw());
        $self->setPitch($player->getPitch());
        return $self;
    }

    public static function fromVector(Vector3 $vector3): self
    {
        return new self($vector3->x, $vector3->y, $vector3->z);
    }

    public static function stringify(string $any): self
    {
        $values = explode(":", $any);
        $self = new self((float)$values[0], (float)$values[1], (float)$values[2]);
        $self->setYaw((float)$values[3]);
        $self->setPitch((float)$values[4]);
        return $self;
    }

    public function parse(): string
    {
        return "$this->x:$this->y:$this->z:$this->yaw:$this->pitch";
    }

    /**
     * @return float|null
     */
    public function getPitch(): ?float
    {
        return $this->pitch;
    }

    /**
     * @param float|null $pitch
     */
    public function setPitch(?float $pitch): void
    {
        $this->pitch = $pitch;
    }
}