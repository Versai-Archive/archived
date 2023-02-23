<?php
declare(strict_types=1);

namespace Versai\vwarps;

use dktapps\pmforms\FormIcon;
use pocketmine\network\mcpe\protocol\types\GameMode;
use pocketmine\player\GameMode as PGM;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Versai\arenas\Arenas;
use Versai\vStaff\EventListener as vStaff;
use InvalidArgumentException;
use function explode;

class Warp{

    private string $name;
    private string $format;
    private Position $position;
    private float $yaw;
    private float $pitch;
    private ?FormIcon $image;
    private bool $pocket;

    /**
     * Warp constructor.
     * @param Position $position
     * @param float $yaw
     * @param float $pitch
     * @param string $name
     * @param string $format
     * @param bool $pocket
     * @param array|null $image
     */
    public function __construct(Position $position, float $yaw, float $pitch, string $name, string $format, bool $pocket = false, array $image = null) {
        $this->setPosition($position);
        $this->name = $name;
        $this->format = $format;
        $this->yaw = $yaw;
        $this->pitch = $pitch;
        $this->pocket = $pocket;
        $this->image = ($image !== null && isset($image['data']) && isset($image['type'])) ? new FormIcon($image['data'], $image['type']) : null;
    }

    /**
     * @param Position $position
     */
    public function setPosition(Position $position): void {
        if($position->getWorld() === null) {
            throw new InvalidArgumentException('Warps must have levels!');
        }
        $this->position = $position;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position {
        return $this->position;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function sendPlayer(Player $player): bool {
        /** @var Main $main */
        $main = Main::getInstance();
        if($this->pocket) {
            if($main->getDeviceOS()->isPE($player)) {
                if(!$player->getGamemode()->equals(PGM::CREATIVE()) || !vStaff::isEnabled($player)) {
                    $player->setGamemode(PGM::SURVIVAL());
                }
                $arena = Arenas::getInstance()->getArenaByName($this->position->getWorld()->getFolderName());
                if($arena !== null) {
                    $arena->teleportToSpawn($player, $this->yaw, $this->pitch);
                    return true;
                }
                $player->teleport($this->position, $this->yaw, $this->pitch);
                return true;
            }
            return false;
        }
        if(!$player->getGamemode()->equals(PGM::CREATIVE()) || !vStaff::isEnabled($player)) {
            $player->setGamemode(PGM::SURVIVAL());
        }

        $arena = Arenas::getInstance()->getArenaByName($this->position->getWorld()->getFolderName());
        if($arena !== null) {
            $arena->teleportToSpawn($player, $this->yaw, $this->pitch);
            return true;
        }
        $player->teleport($this->position, $this->yaw, $this->pitch);
        return true;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFormat(): string {
        return $this->format;
    }

    /**
     * @return bool
     */
    public function isPocket(): bool {
        return $this->pocket;
    }

    /**
     * @return FormIcon|null
     */
    public function getImage(): ?FormIcon {
        return $this->image;
    }

    /**
     * @return array
     */
    public function toConfigEntry(): array {
        $position = $this->getPosition();
        $x = $position->getX();
        $y = $position->getY();
        $z = $position->getZ();
        $image = $this->getImage();
        return [
              'Position' => "$x:$y:$z",
              'Level' => $position->getWorld()->getDisplayName(),
              'Yaw' => $this->yaw,
              'Pitch' => $this->pitch,
              'Pocket' => $this->pocket,
              'Image' => $image === null ? [] : $image->jsonSerialize(),
              'Format' => explode(TextFormat::EOL, $this->getFormat())
        ];
    }
}
