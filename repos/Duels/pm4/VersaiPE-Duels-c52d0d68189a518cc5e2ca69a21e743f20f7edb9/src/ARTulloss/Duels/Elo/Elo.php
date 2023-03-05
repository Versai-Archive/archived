<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/1/2019
 * Time: 3:07 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Elo;

use ARTulloss\Duels\Duels;
use ARTulloss\Duels\Queries\Queries;
use ARTulloss\Duels\Utilities\Utilities;
use ARTulloss\Utils\Config;

/**
 * Class Elo
 * @package ARTulloss\Duels\Elo
 */
class Elo{

    public const DEFAULT_ELO = 500;

    /** @var Duels $duels */
	private $duels;
	/**
	 * Elo constructor.
	 * @param Duels $duels
	 */
	public function __construct(Duels $duels) {
		$this->duels = $duels;
	}
    /**
     * @param string $kitType
     * @param string $playerName
     * @param callable $onFinish
     */
	public function selectElo(string $kitType, string $playerName, callable $onFinish): void{
		$this->duels->getDatabase()->executeSelect(Queries::SELECT_ELO, ['player_name' => $playerName, 'kit' => $kitType], $onFinish, Utilities::getOnError($this->duels));
	}
    /**
     * @param string $playerName
     * @param callable $onFinish
     */
	public function selectAllElo(string $playerName, callable $onFinish): void{
	    $this->duels->getDatabase()->executeSelect(Queries::SELECT_ALL_ELO, ['player_name' => $playerName], $onFinish, Utilities::getOnError($this->duels));
    }
    /**
     * @param string $kit
     * @param int $amount
     * @param callable $onFinish
     */
	public function selectTop(string $kit, int $amount, callable $onFinish) {
	    $this->duels->getDatabase()->executeSelect(Queries::SELECT_TOP, ['kit' => $kit, 'amount' => $amount], $onFinish, Utilities::getOnError($this->duels));
	}
	/**
	 * @param string $kitType
	 * @param string $playerName
	 * @param int $newElo
     * @param callable|null $onFinish
	 */
	public function setElo(string $kitType, string $playerName, int $newElo, ?callable $onFinish): void{
		$this->duels->getDatabase()->executeChange(Queries::INSERT_ELO, ['player_name' => $playerName, 'kit' => $kitType, 'elo' => $newElo], $onFinish, Utilities::getOnError($this->duels));
	}
}