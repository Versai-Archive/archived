<?php

namespace ethaniccc\VAC\util;

use pocketmine\block\Block;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use function max;
use function sqrt;

class AABB extends AxisAlignedBB {

	public $minX, $minY, $minZ;
	public $maxX, $maxY, $maxZ;
	public $minVector, $maxVector;

	public function __construct(float $minX, float $minY, float $minZ, float $maxX, float $maxY, float $maxZ) {
		parent::__construct($minX, $minY, $minZ, $maxX, $maxY, $maxZ);
		$this->minVector = new Vector3($this->minX, $this->minY, $this->minZ);
		$this->maxVector = new Vector3($this->maxX, $this->maxY, $this->maxZ);
	}

	public static function fromAxisAlignedBB(AxisAlignedBB $alignedBB): AABB {
		return new AABB($alignedBB->minX - 0.1, $alignedBB->minY, $alignedBB->minZ - 0.1, $alignedBB->maxX + 0.1, $alignedBB->maxY, $alignedBB->maxZ + 0.1);
	}

	public static function fromPosition(Vector3 $pos, float $width = 0.3, float $height = 1.8): AABB {
		return new AABB($pos->x - $width, $pos->y, $pos->z - $width, $pos->x + $width, $pos->y + $height, $pos->z + $width);
	}

	/**
	 * @author senqai / senpayeh
	 * @link https://github.com/senqai/Sirius/blob/master/src/senpayeh/api/sirius/utils/blockbox/BlockBoxManager.php#L36
	 */
	public static function fromBlock(Block $block): AABB {
		$b = $block->getBoundingBox();
		if ($b !== null) {
			return new AABB($b->minX, $b->minY, $b->minZ, $b->maxX, $b->maxY, $b->maxZ);
		} else {
			return new AABB($block->getX(), $block->getY(), $block->getZ(), $block->getX() + 1, $block->getY() + 1, $block->getZ() + 1);
		}
	}

	public function clone(): AABB {
		return clone $this;
	}

	public function translate(float $x, float $y, float $z): AABB {
		return new AABB($this->minX + $x, $this->minY + $y, $this->minZ + $z, $this->maxX + $x, $this->maxY, $this->maxZ);
	}

	public function grow(float $x, float $y, float $z): AABB {
		return new AABB($this->minX - $x, $this->minY - $y, $this->minZ - $z, $this->maxX + $x, $this->maxY, $this->maxZ);
	}

	public function stretch(float $x, float $y, float $z): AABB {
		return new AABB($this->minX, $this->minY, $this->minZ, $this->maxX + $x, $this->maxY, $this->maxZ);
	}

	public function contains(Vector3 $pos): bool {
		return $pos->getX() <= $this->maxX && $pos->getY() <= $this->maxY && $pos->getZ() <= $this->maxZ && $pos->getX() >= $this->minX && $pos->getY() >= $this->minY && $pos->getZ() >= $this->minZ;
	}

	public function min(int $i): float {
		return [$this->minX, $this->minY, $this->minZ][$i] ?? 0;
	}

	public function max(int $i): float {
		return [$this->maxX, $this->maxY, $this->maxZ][$i] ?? 0;
	}

	public function getCornerVectors(): array {
		return [                                                                                                                                                                                                            // top vectors
			new Vector3($this->maxX, $this->maxY, $this->maxZ), new Vector3($this->minX, $this->maxY, $this->maxZ), new Vector3($this->minX, $this->maxY, $this->minZ), new Vector3($this->maxX, $this->maxY, $this->minZ), // bottom vectors
			new Vector3($this->maxX, $this->minY, $this->maxZ), new Vector3($this->minX, $this->minY, $this->maxZ), new Vector3($this->minX, $this->minY, $this->minZ), new Vector3($this->maxX, $this->minY, $this->minZ)];
	}

	public function distanceFromVector(Vector3 $vector): float {
		$distX = max($this->minX - $vector->x, max(0, $vector->x - $this->maxX));
		$distY = max($this->minY - $vector->y, max(0, $vector->y - $this->maxY));
		$distZ = max($this->minZ - $vector->z, max(0, $vector->z - $this->maxZ));
		return sqrt(($distX ** 2) + ($distY ** 2) + ($distZ ** 2));
	}

	public function calculateIntercept(Vector3 $pos1, Vector3 $pos2): ?RayTraceResult {
		return $this->expandedCopy(0.3, 0.3, 0.3)->isVectorInside($pos1) ? null : parent::calculateIntercept($pos1, $pos2);
	}

}