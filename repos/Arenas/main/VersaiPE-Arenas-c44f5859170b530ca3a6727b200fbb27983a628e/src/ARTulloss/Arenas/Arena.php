<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 12/12/2018
 * Time: 2:37 PM
 */
declare(strict_types=1);

namespace ARTulloss\Arenas;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use function implode;

/**
 * Class Arena
 * @package ARTulloss\Arenas
 */
class Arena
{
	/** @var string $name */
	private $name;
	/** @var array $ids */
	private $ids;
	/** @var Position $location */
	private $location;
	/** @var float $protection */
	private $protection;
	/** @var array $settings */
	private $settings;

	/**
	 * Arena constructor.
	 * @param string $name
	 * @param array $ids
	 * @param Position $location
	 * @param array $settings
	 */
	public function __construct(string $name, array $ids, Position $location, array $settings)
	{
		$this->name = $name;
		$this->ids = $ids;
		$this->location = $location;
		$this->settings = $settings;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return Position
	 */
	public function getLocation(): Position
	{
		return $this->location;
	}

	/**
	 * @param Vector3 $vector3
	 */
	public function setLocation(Vector3 $vector3): void
	{
		$this->location = $vector3;
	}

	/**
	 * @return int
	 */
	public function getProtection(): ?int
	{
		if (isset($this->settings['protection']))
			return $this->settings['protection'];
		return 0;
	}

	/**
	 * @param int $protection
	 */
	public function setProtection(int $protection): void
	{
		$this->protection = $protection;
	}

	/**
	 * @param bool $breakable
	 */
	public function setBreakable(bool $breakable): void
	{
		$this->settings['breakable'] = $breakable;
	}

	/**
	 * @return bool
	 */
	public function isBreakable(): bool
	{
		return $this->settings['breakable'];
	}

    /**
     * @return array
     */
    public function getBreakableList(): array
    {
        return $this->settings['break-list'];
    }

	/**
	 * @param bool $placeable
	 */
	public function setPlaceable(bool $placeable): void
	{
		$this->settings['placeable'] = $placeable;
	}

	/**
	 * @return bool
	 */
	public function isPlaceable(): bool
	{
		return $this->settings['placeable'];
	}

    /**
     * @return array
     */
	public function getPlaceableList(): array
    {
	    return $this->settings['place-list'];
    }

	/**
	 * @param bool $pickup
	 */
	public function setCanPickUpItems(bool $pickup): void
	{
		$this->settings['pickup-items'] = $pickup;
	}

	/**
	 * @return bool
	 */
	public function canPickUpItems(): bool
	{
		return $this->settings['pickup-items'];
	}

	/**
	 * @param float $kb
	 */
	public function setKnockback(float $kb): void
	{
		$this->settings['knockback'] = $kb;
	}

	/**
	 * @return float
	 */
	public function getKnockback(): float
	{
		return $this->settings['knockback'];
	}

	/**
	 * @param int $hitCooldown
	 */
	public function setHitCooldown(int $hitCooldown): void
	{
		$this->settings['hitCooldown'] = $hitCooldown;
	}

	/**
	 * @return int
	 */
	public function getHitCooldown(): int
	{
		return $this->settings['hitCooldown'];
	}

	/**
	 * @param bool $fallDamage
	 */
	public function setFallDamage(bool $fallDamage): void
	{
		$this->settings['fallDamage'] = $fallDamage;
	}

	/**
	 * @return bool
	 */
	public function hasFallDamage(): bool
	{
		return $this->settings['fallDamage'];
	}

	/**
	 * @param bool $hunger
	 */
	public function setHunger(bool $hunger): void
	{
		$this->settings['hunger'] = $hunger;
	}

	/**
	 * @return bool
	 */
	public function hasHunger(): bool
	{
		return $this->settings['hunger'];
	}

	/**
	 * @param bool $lightning
	 */
	public function setLightning(bool $lightning): void
	{
		$this->settings['lightning'] = $lightning;
	}

	/**
	 * @return bool
	 */
	public function hasLightning(): bool
	{
		return $this->settings['lightning'];
	}

	/**
	 * @param bool $explosive
	 */
	public function setExplosion(bool $explosive): void
	{
		$this->settings['explosion'] = $explosive;
	}

	/**
	 * @return bool
	 */
	public function hasExplosion(): bool
	{
		return $this->settings['explosion'];
	}

    /**
     * @return int
     */
	public function getBuildLimit(): int
    {
	    return ($this->settings['build-limit'] ? $this->settings['build-limit'] : 128);
    }

	/**
	 * @return array
	 */
	public function getAll(): array
	{
		return ["IDs" => $this->getIds(), "spawn" => implode(":", [$this->location->getX(), $this->location->getY(), $this->location->getZ()]), $this->settings];
	}

	/**
	 * @return array
	 */
	public function getIds(): array
	{
		return $this->ids;
	}

}