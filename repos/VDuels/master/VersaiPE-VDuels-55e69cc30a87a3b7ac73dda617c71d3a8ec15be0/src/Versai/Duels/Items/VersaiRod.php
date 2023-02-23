<?php
declare(strict_types = 1);

namespace Versai\Duels\Items;

use pocketmine\entity\Location;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\Durable;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\LaunchSound;
use Versai\Duels\Items\Entity\FishingHook;

class VersaiRod extends Durable {

    private ItemIdentifier $id;

    public function __construct(ItemIdentifier $id, string $name = 'Versai Rod'){
        parent::__construct($id, $name);
    }

    public function getMaxStackSize(): int{
        return 1;
    }

    public function getMaxDurability(): int{
        return 355;
    }

    public function getCooldownTicks(): int{
        return 8;
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult{

        $projectile = new FishingHook(Location::fromObject($player->getPosition()->add(0, $player->getEyeHeight(), 0), $player->getWorld(), $player->getLocation()->yaw, $player->getLocation()->pitch), $player);
        $projectile->setMotion($player->getDirectionVector()->multiply($this->getThrowForce()));

        $projectileEv = new ProjectileLaunchEvent($projectile);
        $projectileEv->call();

        if ($projectileEv->isCancelled()) {
            $projectile->flagForDespawn();
        } else {
            $projectile->spawnToAll();
            $player->getWorld()->addSound($player->getPosition(), new LaunchSound(), $player->getViewers());
        }
        return ItemUseResult::SUCCESS();
    }

    public function getProjectileEntityType(): string{
        return "FishingHook";
    }

    public function getThrowForce(): float{
        return 1.5;
    }
}