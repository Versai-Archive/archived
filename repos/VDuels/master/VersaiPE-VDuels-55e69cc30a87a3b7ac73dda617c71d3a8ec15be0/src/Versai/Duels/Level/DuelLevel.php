<?php
declare(strict_types=1);

namespace Versai\Duels\Level;

use pocketmine\world\World;

class DuelLevel {

    private string $name;
	private array $positions;
	private World $level;
	private string $author;
	private array $ids;

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
	 * @return array
	 */
	public function getPositions(): array {
		return $this->positions;
	}

	/**
	 * @return World
	 */
	public function getLevel(): World{
		return $this->level;
	}

	/**
	 * @return int[]
	 */
	public function getIDs(): array {
		return $this->ids;
	}

	/**
	 * @return string
	 */
	public function getAuthor(): string {
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