<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

/**
 * Noise classes used in world generation
 */
namespace pocketmine\world\generator;

use pocketmine\utils\Random;
use pocketmine\utils\Utils;
use pocketmine\world\ChunkManager;
use function ctype_digit;

abstract class Generator{

	/**
	 * Converts a string world seed into an integer for use by the generator.
	 *
	 * @param string $seed
	 *
	 * @return int|null
	 */
	public static function convertSeed(string $seed) : ?int{
		if($seed === ""){ //empty seed should cause a random seed to be selected - can't use 0 here because 0 is a valid seed
			$convertedSeed = null;
		}elseif(ctype_digit($seed)){ //this avoids treating seeds like "404.4" as integer seeds
			$convertedSeed = (int) $seed;
		}else{
			$convertedSeed = Utils::javaStringHash($seed);
		}

		return $convertedSeed;
	}

	/** @var ChunkManager */
	protected $world;
	/** @var int */
	protected $seed;
	/** @var array */
	protected $options;

	/** @var Random */
	protected $random;

	/**
	 * @param ChunkManager $world
	 * @param int          $seed
	 * @param array        $options
	 *
	 * @throws InvalidGeneratorOptionsException
	 */
	public function __construct(ChunkManager $world, int $seed, array $options = []){
		$this->world = $world;
		$this->seed = $seed;
		$this->options = $options;
		$this->random = new Random($seed);
	}

	abstract public function generateChunk(int $chunkX, int $chunkZ) : void;

	abstract public function populateChunk(int $chunkX, int $chunkZ) : void;
}
