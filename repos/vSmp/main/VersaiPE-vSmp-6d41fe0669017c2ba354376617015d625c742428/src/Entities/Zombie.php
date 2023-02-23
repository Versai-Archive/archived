<?php

declare(strict_types=1);

namespace Versai\RPGCore\Entities;

use alvin0319\CustomItemLoader\items\SMPItemIds;
use alvin0319\CustomItemLoader\items\SMPItemLoader;
use pocketmine\entity\Living;
use pocketmine\item\VanillaItems;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\entity\Location;
use pocketmine\Server;
use function mt_rand;
use Versai\RPGCore\Libraries\pathfinder\algorithm\AlgorithmSettings;
use Versai\RPGCore\Libraries\pathfinder\entity\navigator\Navigator;
use Versai\RPGCore\Libraries\pathfinder\Pathfinder;
use Throwable;
use function array_key_first;
use function intval;
use alvin0319\CustomItemLoader\CustomItemManager;

class Zombie extends Living{

	public function __construct(Location $location, ?CompoundTag $nbt = null){
        $this->navigator = new Navigator($this, null, null,
            (new AlgorithmSettings())
                ->setTimeout(0.05)
                ->setMaxTicks(0)
        );
        parent::__construct($location, $nbt);

        $this->setScale(1);
    }

	public static function getNetworkTypeId() : string{ return EntityIds::ZOMBIE; }

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.8, 0.6);
	}

	public function getName() : string{
		return "Zombie";
	}

	public function getDrops() : array{
		$drops = [
			VanillaItems::ROTTEN_FLESH()->setCount(mt_rand(0, 2))
		];

		if(mt_rand(0, 199) < 5){
			switch(mt_rand(0, 3)){
				case 0:
					$drops[] = VanillaItems::IRON_INGOT();
					break;
				case 1:
					$drops[] = VanillaItems::CARROT();
					break;
				case 2:
					$drops[] = VanillaItems::POTATO();
					break;
				case 3:
					if (mt_rand(0, 100) == 3) {
						$drops[] = SMPItemLoader::getItem(SMPItemIds::BASIC_RUNE);
					}
				break;
			}
		}
		return $drops;
	}

	public function onUpdate(int $currentTick): bool {
		$this->navigator->setSpeed(0.23);
		$target = Server::getInstance()->getOnlinePlayers()[array_key_first(Server::getInstance()->getOnlinePlayers())] ?? null;
        if($target === null) return parent::onUpdate($currentTick);
        $position = $target->getPosition();
        $targetVector3 = $this->navigator->getTargetVector3();
        if(!$position->world->isInWorld(intval($position->x), intval($position->y), intval($position->z))){
            return parent::onUpdate($currentTick);
        }

        if($this->navigator->getTargetVector3() === null || $targetVector3->distanceSquared($position) > 1) {
            $this->navigator->setTargetVector3($position);
        }
		try {
			$this->navigator->onUpdate();
		} catch (Throwable $e) {
			$this->flagForDespawn();
			Pathfinder::$instance->getLogger()->logException($e);
		}
        return parent::onUpdate($currentTick);
	}

	public function getXpDropAmount() : int{
		return 5;
	}
}