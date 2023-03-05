<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 12/28/2018
 * Time: 5:45 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Level;


use pocketmine\world\Position;
use pocketmine\world\World;

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
	/** @var World $level */
	private $level;
	/** @var string $author */
	private $author;
	/** @var int[] */
	private $ids;

    /**
     * DuelLevel constructor.
     * @param $name
     * @param array $positions
     * @param World $level
     * @param string $author
     * @param array $ids
     */
	public function __construct($name, array $positions, World $level, string $author, array $ids)
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
	 * @return World
	 */
	public function getLevel(): World
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