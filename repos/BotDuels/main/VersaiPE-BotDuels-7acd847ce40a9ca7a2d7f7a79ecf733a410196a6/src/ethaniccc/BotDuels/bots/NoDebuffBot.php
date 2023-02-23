<?php

namespace ethaniccc\BotDuels\bots;

use ethaniccc\BotDuels\BotDuels;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

/**
 * Class Bot
 * If any code is taken from here, please ensure proper credit is given to me :)
 * Discord: ethaniccc#1659
 * Github: https://github.com/ethaniccc
 */
class NoDebuffBot extends Human {

	public const DIFFICULTY_EASY = 0;
	public const DIFFICULTY_MEDIUM = 1;
	public const DIFFICULTY_HARD = 2;
	public const DIFFICULTY_EXPERT = 3;
	public const DIFFICULTY_CLOSET_HACKER = 4;
	public const DIFFICULTY_BLATANT_HACKER = 5;

	public $difficulties = [
	    0 => "easy",
        1 => "medium",
        2 => "hard",
        3 => "expert",
        4 => "closet-cheater",
        5 => "blatant-cheater"
    ];

	public $pearlCount = 16;
	public $reachDist;
	/** @var int */
	public $potionCount = 32;
	public $potsInHotbar = 7;
	public $refillTicks;
	public $refillCooldown = 0;
	public $potTicks;

	// settings you can customize for different bots
	public $potCooldown = 0;
	// default minecraft reach is 3, this accurately calculates reach with raycasting
	public $canPearl = true;
	public $pearlCooldown = 300;
	public $ticksUntilCanPearl = 0;
	public $canAgroPearl = true;
	public $lieniant = false;
	/** @var float */
	protected $moveForward = 0.0, $moveStrafe = 0.0;
	/** @var float */
	protected $randomYawVelocity = 0.0;
	/** @var Vector3|null */
	protected $knockbackMotion;
	protected $jumpTicks = 0;
	protected $hasBeenAttacked = false;
	protected $ticksOffGround = 0;
	protected $difficulty;
	/** @var string */
	private $target;
	/** @var Player */
	private $player;
	/** @var int */
	private $attackTicks = 0;
	/** @var int */
	private $nextStrafeTicks = 40;
	/** @var Vector3[] */
	private $playerLocations = [];
	/** @var Vector3 */
	private $currentPlayerLocation;
	/** @var int */
	private $newPosRotationIncrements = 3;
	/** @var Vector3 */
	private $previousLocation;
	/** @var int */
	private $teleportTicks = 0;
	/** @var int */
	private $wtapTicks = 0;

	public function __construct(Level $level, Skin $skin, CompoundTag $nbt, string $target, int $difficulty = self::DIFFICULTY_EXPERT) {
		$this->setSkin($skin);
		parent::__construct($level, $nbt);
		$this->target = $target;
		$this->giveFightItems();
		$finalDiff = $this->difficulties[$difficulty];
		$data = BotDuels::getInstance()->getConfig()->getNested("bot-data.nodebuff.$finalDiff");
		$this->reachDist = $data["reachDist"];
		$this->canPearl = $data["canPearl"];
		$this->canAgroPearl = $data["canAgroPearl"];
		$this->refillTicks = $data["refillTicks"];
		$this->potTicks = $data["potTicks"];
		$this->wtapTicks = $data["wtapTicks"];
		if($finalDiff === "easy"){
            $this->nextStrafeTicks = PHP_INT_MAX;
        }
		$this->setNameTag(TextFormat::GREEN . $data["name"] . TextFormat::RESET);

		$this->difficulty = $difficulty;
		$this->potCooldown = -100;
		$target = $this->getTargetPlayer();
		$this->lieniant = BotDuels::getInstance()->isMobile[spl_object_hash($target)] ?? true;
		$this->stepHeight = 1.0;
	}

	// customize this as needed
	private function giveFightItems(): void {
		$sword = Item::get(Item::DIAMOND_SWORD);
		$sword->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10));
		$this->getInventory()->setItem(0, $sword);
		$this->getInventory()->setItem(1, Item::get(ItemIds::ENDER_PEARL, 1, $this->pearlCount));
		$this->getInventory()->setItem(2, Item::get(ItemIds::SPLASH_POTION, 22));
		$this->getArmorInventory()->setHelmet(Item::get(ItemIds::DIAMOND_HELMET));
		$this->getArmorInventory()->setChestplate(Item::get(ItemIds::DIAMOND_CHESTPLATE));
		$this->getArmorInventory()->setLeggings(Item::get(ItemIds::DIAMOND_LEGGINGS));
		$this->getArmorInventory()->setBoots(Item::get(ItemIds::DIAMOND_BOOTS));
	}

	public function getTargetPlayer(): ?Player {
		if ($this->player === null) {
			$this->player = Server::getInstance()->getPlayer($this->target);
		}
		return $this->player;
	}

	public function entityBaseTick(int $tickDiff = 1): bool {
		$target = $this->getTargetPlayer();
		if ($target === null) {
			$this->flagForDespawn();
			return parent::entityBaseTick($tickDiff);
		} else {
			if (!$target->isAlive() || !$target->isConnected() || $target->isFlaggedForDespawn()) {
				$this->flagForDespawn();
				return parent::entityBaseTick($tickDiff);
			}
		}

		--$this->attackTicks;
		--$this->potCooldown;
		--$this->ticksUntilCanPearl;
		--$this->refillCooldown;
		--$this->jumpTicks;
		--$this->teleportTicks;
		--$this->wtapTicks;

		// convert ping to ticks
		if (count($this->playerLocations) >= floor($target->getPing() / 50)) {
			array_shift($this->playerLocations);
		}
		$this->playerLocations[] = $target->asVector3();
		$lastLocation = $this->playerLocations[count($this->playerLocations) - 1];

		if ($this->currentPlayerLocation === null) {
			$this->currentPlayerLocation = $lastLocation;
			$this->newPosRotationIncrements = 3;
		} else {
			if ($this->previousLocation->equals($lastLocation)) {
				--$this->newPosRotationIncrements;
			} else {
				$this->newPosRotationIncrements = 3;
			}
			if ($this->newPosRotationIncrements > 0) {
				$this->currentPlayerLocation->x += (($lastLocation->x - $this->currentPlayerLocation->x) / $this->newPosRotationIncrements);
				$this->currentPlayerLocation->y += (($lastLocation->y - $this->currentPlayerLocation->y) / $this->newPosRotationIncrements);
				$this->currentPlayerLocation->z += (($lastLocation->z - $this->currentPlayerLocation->z) / $this->newPosRotationIncrements);
			}
			// $target->sendMessage("{$this->newPosRotationIncrements}");
		}

		$lastLocation = clone $this->currentPlayerLocation;
		$this->previousLocation = clone $lastLocation;

		if ($this->refillCooldown === 0) {
			$this->potsInHotbar = 7;
		}

		if ($this->onGround) {
			$this->ticksOffGround = 0;
		} else {
			++$this->ticksOffGround;
		}

		if ($this->refillCooldown <= 0) {
			if ($this->ticksUntilCanPearl <= $this->pearlCooldown - 30) {
				$this->getInventory()->setHeldItemIndex(0);
			}

			$this->moveForward = 1.0;
			if ($this->distanceSquared($target) <= 36 && $this->potCooldown <= 0 && !$this->lieniant) {
				if (--$this->nextStrafeTicks <= 0) {
					if ($this->potCooldown > -20) {
						$this->moveStrafe = 0.0;
					} else {
						$this->moveStrafe = mt_rand(-1, 1);
					}
					$this->nextStrafeTicks = 40;
				}
			} else {
				$this->moveStrafe = 0.0;
			}

			if (abs($this->moveForward) > 0 && abs($this->moveStrafe) > 0) {
				$this->moveForward *= 0.788;
				$this->moveStrafe *= 0.788;
			}

			if ($this->potCooldown < -$this->potTicks) {
				// calculate where the entity should be looking at
				$horizontal = sqrt(($lastLocation->x - $this->x) ** 2 + ($lastLocation->z - $this->z) ** 2);
				$vertical = $lastLocation->y - $this->y;
				$pitch = -atan2($vertical, $horizontal) / M_PI * 180; // negative is up, positive is down
				$xDist = $lastLocation->x - $this->x;
				$zDist = $lastLocation->z - $this->z;
				$yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
				if ($yaw < 0) {
					$yaw += 360.0;
				}

				if ($this->getNextRand() < 0.02) {
					$this->randomYawVelocity = $this->getNextRand();
				}

				$yaw -= $this->randomYawVelocity;
				$pitch += $this->randomYawVelocity;

				$this->randomYawVelocity *= 0.9;

				$this->setRotation($yaw, $pitch);
			}

			$this->moveForward *= 0.98;
			$this->moveStrafe *= 0.98;

			if ($this->canPearl && $this->ticksUntilCanPearl <= 0 && $this->hasBeenAttacked) {
				if ($this->canAgroPearl) {
					if (($target->getHealth() <= 10 && $target->distance($this) >= 5) || $this->ticksOffGround >= 40 || ($this->getPlayerAngleToBot() >= 140 && $target->distance($this) >= 7)) {
						$this->pearl();
					}
				} else {
					if ($this->getPlayerAngleToBot() >= 140 && $target->distance($this) >= 10) {
						$this->pearl();
					}
				}
			}

			if ($this->getHealth() <= 5.0 && $this->potCooldown <= 0) {
				$this->pot();
			}
		} else {
			$this->moveForward = 0.0;
			$this->moveStrafe = 0.0;
		}

		if ($this->knockbackMotion !== null) {
			$this->knockbackMotion->x *= 0.81;
			$this->knockbackMotion->z *= 0.81;
			if ($this->onGround) {
				$this->knockbackMotion->x *= 0.6;
				$this->knockbackMotion->z *= 0.6;
			}
			if (abs($this->knockbackMotion->x) < 0.005 || abs($this->knockbackMotion->z) < 0.005) {
				$this->knockbackMotion = null;
			}
		} elseif ($this->refillCooldown <= 0 && $this->teleportTicks < 0 && $this->wtapTicks < 0) {
			$this->setSpeed(0.7);
		}

		// replication of EntityLivingBase->moveEntityWithHeading()
		// lowered the friction a significant amount lower so it's (in my opinion) better
		$friction = 0.81;
		if ($this->onGround) {
			// just assume normal block friction....
			$friction *= 0.6;
		}

		$AABB = new AxisAlignedBB($lastLocation->x - 0.4, $lastLocation->y, $lastLocation->z - 0.4, $lastLocation->x + 0.4, $lastLocation->y + 1.9, $lastLocation->z + 0.4);

		if ($this->refillCooldown <= 0) {
			// only raycast every 5 ticks to save CPU
			if ($this->distanceSquared($target) <= 100 && $this->attackTicks <= 0 && $this->potCooldown <= 0) {
				$this->broadcastEntityEvent(4);
				$canHit = ($raycast = $AABB->calculateIntercept($this->add(0, $this->eyeHeight, 0), $this->add(0, $this->eyeHeight, 0)->add($this->getDirectionVector()->multiply(10)))) !== null && $raycast->getHitVector()->distanceSquared($this->add(0, $this->eyeHeight, 0)) <= $this->reachDist ** 2;
				if ($canHit) {
					$event = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getInventory()->getItemInHand()->getAttackPoints());
					if (!$this->isSprinting() && $this->fallDistance > 0 && !$this->hasEffect(Effect::BLINDNESS) && !$this->isUnderwater()) {
						$event->setModifier($event->getFinalDamage() / 2, EntityDamageEvent::MODIFIER_CRITICAL);
					}
					$target->attack($event);
					if (!$event->isCancelled()) {
						$this->wtapTicks = 3;
					}
				}
				$this->attackTicks = 5;
			}
		}

		if ($this->onGround) {
			$f = 0.13 * (0.16277136 / ($friction * $friction * $friction));
		} else {
			$f = 0.026;
		}

		$this->moveFlying($this->moveForward, $this->moveStrafe, $f);

		$this->motion->y -= 0.08;
		$this->motion->y *= 0.9800000190734863;
		$this->motion->x *= $friction;
		$this->motion->z *= $friction;

		if (abs($this->motion->x) < 0.005) {
			$this->motion->x = 0;
		}
		if (abs($this->motion->y) < 0.005) {
			$this->motion->y = 0;
		}
		if (abs($this->motion->z) < 0.005) {
			$this->motion->z = 0;
		}

		return parent::entityBaseTick($tickDiff);
	}

	private function getNextRand(): float {
		return mt_rand() / mt_getrandmax();
	}

	private function getPlayerAngleToBot(): float {
		if (($player = $this->getTargetPlayer()) !== null) {
			$xDist = $this->x - $player->x;
			$zDist = $this->z - $player->z;
			$yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
			if ($yaw < 0) {
				$yaw += 360.0;
			}
			return abs(abs($yaw) - abs($player->getYaw()));
		}
		return 0.0;
	}

	private function pearl(): void {
		if (($target = $this->getTargetPlayer()) !== null && $this->pearlCount > 0) {
			$expectedPosition = $target->getPosition();
			$horizontal = sqrt(($expectedPosition->x - $this->x) ** 2 + ($expectedPosition->z - $this->z) ** 2);
			$vertical = $expectedPosition->y - $this->y;
			$pitch = -atan2($vertical, $horizontal) / M_PI * 180; // negative is up, positive is down
			$xDist = $expectedPosition->x - $this->x;
			$zDist = $expectedPosition->z - $this->z;
			$yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
			if ($yaw < 0) {
				$yaw += 360.0;
			}
			$pitch -= min(hypot($target->x - $this->x, $target->z - $this->z) - 15.0, 30.0);
			if ($pitch < -45) {
				$pitch = -45;
			} elseif ($pitch > 45) {
				$pitch = 45;
			}
			$this->yaw = $yaw;
			$this->pitch = $pitch;
			$this->getInventory()->setHeldItemIndex(1);
			$nbt = Entity::createBaseNBT($this->add(0, 1.62, 0), $this->getDirectionVector()->multiply(1.5), $this->yaw, $this->pitch);
			$pearl = Entity::createEntity("ThrownEnderpearl", $this->getLevelNonNull(), $nbt, $this);
			$pearl->spawnToAll();
			--$this->pearlCooldown;
			$this->ticksUntilCanPearl = $this->pearlCooldown;
		}
	}

	private function pot(): void {
		if ($this->potionCount > 0) {
			$lastLocation = clone $this->currentPlayerLocation;
			$xDist = $lastLocation->x - $this->x;
			$zDist = $lastLocation->z - $this->z;
			$yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
			if ($yaw < 0) {
				$yaw += 360.0;
			}
			$this->getInventory()->setHeldItemIndex(2);
			$this->setRotation(fmod($yaw - 180, 360), $this->onGround ? 15 : -15);
			$this->getLevel()->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_GLASS);
			$this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_PARTICLE_SPLASH, Effect::getEffect(Effect::INSTANT_HEALTH)->getColor()->toARGB());
			$this->setHealth($this->getHealth() + mt_rand(5, 11));

			$this->potCooldown = $this->potTicks;
			--$this->potionCount;
			--$this->potsInHotbar;
			if ($this->potsInHotbar === 0) {
				$this->refillCooldown = $this->refillTicks;
			}
		}
	}

	protected function setSpeed(float $speed): void {
		$directionSpeed = $this->getDirectionSpeed();
		$this->motion->x = -sin($directionSpeed) * $speed;
		$this->motion->z = cos($directionSpeed) * $speed;
	}

	// returns a random float from 0.0 - 1.0

	private function getDirectionSpeed(): float {
		$direction = abs($this->yaw);
		if ($this->moveForward < 0)
			$direction += 180;
		$forward = 1.0;
		if ($this->moveForward < 0)
			$forward = -0.5; elseif ($this->moveForward > 0)
			$forward = 0.5;
		if ($this->moveStrafe > 0)
			$direction -= 90 * $forward; elseif ($this->moveStrafe < 0)
			$direction += 90 * $forward;
		$direction *= 0.017453292;
		return $direction;
	}

	protected function moveFlying(float $forward, float $strafe, float $friction): void {
		$var4 = $forward * $forward + $strafe * $strafe;
		if ($var4 >= 1E-4) {
			$var4 = sqrt($var4);
			if ($var4 < 1.0) {
				$var4 = 1.0;
			}
			$var4 = $friction / $var4;
			$forward *= $var4;
			$strafe *= $var4;
			$var5 = sin($this->yaw * M_PI / 180);
			$var6 = cos($this->yaw * M_PI / 180);
			$this->motion->x += ($forward * $var6 - $strafe * $var5);
			$this->motion->z += ($strafe * $var6 - $forward * $var5);
		}
	}

	public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null): bool {
		$this->teleportTicks = 3;
		return parent::teleport($pos, $yaw, $pitch); // TODO: Change the autogenerated stub
	}

	public function jump(): void {
		$this->jumpTicks = 10;
		$this->moveStrafe = 0.0;
		parent::jump();
	}

	public function setMotion(Vector3 $motion): bool {
		if ($motion->y > 0 && ($motion->y < 0.3 || abs($motion->y - 0.4) < 0.05)) {
			$motion->y = 0.385;
		}
		if (abs($motion->x) < 0.05 || abs($motion->z) < 0.05) {
			$isXGreater = abs($motion->x) > abs($motion->z);
			if ($isXGreater) {
				$motion->x = $motion->x >= 0 ? 0.37 : -0.37;
				$motion->z = $motion->z >= 0 ? 0.115 : -0.115;
			} else {
				$motion->z = $motion->z >= 0 ? 0.37 : -0.37;
				$motion->x = $motion->x >= 0 ? 0.115 : -0.115;
			}
		}
		$motion->x *= 0.7;
		$motion->z *= 0.7;
		$valid = parent::setMotion($motion);
		if ($valid) {
			$this->knockbackMotion = $motion;
		}
		return $valid;
	}

	public function attack(EntityDamageEvent $source): void {
		$this->hasBeenAttacked = true;
		parent::attack($source);
	}

	protected function checkGroundState(float $movX, float $movY, float $movZ, float $dx, float $dy, float $dz): void {
		$this->isCollidedVertically = $movY != $dy;
		$this->isCollidedHorizontally = ($movX != $dx or $movZ != $dz);
		$this->isCollided = ($this->isCollidedHorizontally or $this->isCollidedVertically);
		$this->onGround = ($movY != $dy and $movY < 0);

		/* $target = $this->getTargetPlayer();
		if ($this->isCollidedHorizontally) {
			if ($target !== null) {
				$target->sendMessage("collided horizontally - diffX=" . ($dx - $movX) . " diffZ=" . ($dz - $movZ));
			}
		} */
	}

	protected function applyGravity(): void {
	}

	protected function tryChangeMovement(): void {
	}

}