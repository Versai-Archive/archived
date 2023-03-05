<?php
declare(strict_types=1);

namespace Versai\Duels\Elo;

use function round;

class Rating {

	/**
	 * K factor
	 */
	public const KFACTOR = 24;

	public const WIN = 1;
	public const DRAW = 0.5;
	public const LOSS = 0;

	/** @var int $ratingA */
	private int $ratingA;
	/** @var int $ratingB */
	private int $ratingB;

	/** @var int $scoreA */
	private int $scoreA;
	/** @var int $scoreB */
	private int $scoreB;

	/**
	 * @param int $ratingA Current rating of A
	 * @param int $ratingB Current rating of B
	 * @param int $scoreA Score of A
	 * @param int $scoreB Score of B
	 */
	public function __construct(int $ratingA, int $ratingB, int $scoreA, int $scoreB)
	{
		$this->ratingA = $ratingA;
		$this->ratingB = $ratingB;
		$this->scoreA = $scoreA;
		$this->scoreB = $scoreB;
	}

	/**
	 * @return array
	 */
	public function calculate(): array
	{
		$expectedA = (10 ** (($this->ratingB - $this->ratingA) / 400) + 1) ** -1;
		$expectedB = (10 ** (($this->ratingA - $this->ratingB) / 400) + 1) ** -1;

		$ratingA = $this->ratingA + Rating::KFACTOR * ($this->scoreA - $expectedA);
		$ratingB = $this->ratingB + Rating::KFACTOR * ($this->scoreB - $expectedB);

		$ratingA = (int) round($ratingA);
		$ratingB = (int) round($ratingB);

		return ['a' => $ratingA, 'b' => $ratingB];
	}
}