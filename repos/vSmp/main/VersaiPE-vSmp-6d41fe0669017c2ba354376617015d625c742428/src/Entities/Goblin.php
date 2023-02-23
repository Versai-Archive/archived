<?php

namespace Versai\RPGCore\Entities;

use pocketmine\entity\EntitySizeInfo;

use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\Server;
use Versai\RPGCore\Libraries\pathfinder\algorithm\AlgorithmSettings;
use Versai\RPGCore\Libraries\pathfinder\entity\navigator\Navigator;
use Versai\RPGCore\Libraries\pathfinder\Pathfinder;

class Goblin extends Living {

    public function __construct(Location $location, ?CompoundTag $nbt = null) {
        $this->navigator = new Navigator($this, null, null,
            (new AlgorithmSettings())
                ->setTimeout(0.05)
                ->setMaxTicks(0)
        );
        parent::__construct($location, $nbt);
        $this->setScale(1);
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(1.8, 0.6);
    }

    public static function getNetworkTypeId(): string {
        return EntityIds::ZOMBIE;
    }

    public function getName(): string {
        return "Goblin";
    }

    public function onUpdate(int $currentTick): bool {
        $this->navigator->setSpeed(0.28);
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
        } catch (\Throwable $e) {
            $this->flagForDespawn();
            Pathfinder::$instance->getLogger()->logException($e);
        }
        return parent::onUpdate($currentTick);
    }

    public function getXpDropAmount() : int{
        return 5;
    }
}