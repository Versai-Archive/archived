<?php
declare(strict_types=1);

namespace ARTulloss\Duels\Level;

use pocketmine\level\Level;
use pocketmine\level\Position;

/**
 * Class Arena
 * @package ARTulloss\Duels\Arena
 */
class DuelLevel
{
    /** @var string $name */
    private $name;
	/** @var Position[] $positions */
	private $positions;
	/** @var Level $level */
	private $level;
	/** @var string $author */
	private $author;
	/** @var int[] */
	private $ids;

    /**
     * DuelLevel constructor.
     * @param $name
     * @param array $positions
     * @param Level $level
     * @param string $author
     * @param array $ids
     */
	public function __construct($name, array $positions, Level $level, string $author, array $ids)
	{
	    $this->name = $name;
		$this->positions = $positions;
		$this->level = $level;
		$this->author = $author;
		$this->ids = $ids;
	}

	/**
	 * @return Position[]
	 */
	public function getPositions(): array
	{
		return $this->positions;
	}

	/**
	 * @param int $i
	 * @return Position
	 */
	public function getPos(int $i): Position
	{
		return $this->positions[$i];
	}

	/**
	 * @return Level
	 */
	public function getLevel(): Level
	{
		return $this->level;
	}

	/**
	 * @return int[]
	 */
	public function getIDs(): array
	{
		return $this->ids;
	}

	/**
	 * @return string
	 */
	public function getAuthor(): string
	{
		return $this->author;
	}

    /**
     * Get the name of the arena as shown in the config
     * @return string
     */
	public function getName(): string {
	    return $this->name;
    }

}