<?php

namespace ethaniccc\VAC\util;

use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use function max;
use function sqrt;

class AABB {

	public float $minX, $minY, $minZ;
	public float $maxX, $maxY, $maxZ;
	public Vector3 $minVector, $maxVector;

	public function __construct(float $minX, float $minY, float $minZ, float $maxX, float $maxY, float $maxZ) {
		$this->minX = $minX;
		$this->minY = $minY;
		$this->minZ = $minZ;
		$this->maxX = $maxX;
		$this->maxY = $maxY;
		$this->maxZ = $maxZ;

		$this->minVector = new Vector3($minX, $minY, $minZ);
		$this->maxVector = new Vector3($maxX, $maxY, $maxZ);
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
		$b = $block->getCollisionBoxes()[0];
		if ($b !== null) {
			return new AABB($b->minX, $b->minY, $b->minZ, $b->maxX, $b->maxY, $b->maxZ);
		} else {
			return new AABB($block->getPosition()->getX(), $block->getPosition()->getY(), $block->getPosition()->getZ(), $block->getPosition()->getX() + 1, $block->getPosition()->getY() + 1, $block->getPosition()->getZ() + 1);
		}
	}

	public function expand(float $x, float $y, float $z): AABB {
		$this->minX -= $x;
		$this->minY -= $y;
		$this->minZ -= $z;
		$this->maxX += $x;
		$this->maxY += $y;
		$this->maxZ += $z;

		return $this;
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

	public function stretch(int $axis, float $distance): AABB {
		if ($axis === Axis::Y) {
			$this->minY -= $distance;
			$this->maxY += $distance;
		} elseif ($axis === Axis::Z) {
			$this->minZ -= $distance;
			$this->maxZ += $distance;
		} elseif ($axis === Axis::X) {
			$this->minX -= $distance;
			$this->maxX += $distance;
		} else {
			throw new InvalidArgumentException("Invalid axis $axis");
		}
		return $this;
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
		return $this->isVectorInside($pos1) ? new RayTraceResult($this, 0, new Vector3(0, 0, 0)) : $this->calculateInterceptB($pos1, $pos2);
	}


	//Copied from AxisAllignedBB since it became a final class and we can't extend it anymore D:

	public function isVectorInside(Vector3 $vector): bool {
		if ($vector->x <= $this->minX or $vector->x >= $this->maxX) {
			return false;
		}
		if ($vector->y <= $this->minY or $vector->y >= $this->maxY) {
			return false;
		}
		return $vector->z > $this->minZ and $vector->z < $this->maxZ;
	}

	public function calculateInterceptB(Vector3 $pos1, Vector3 $pos2): ?RayTraceResult {
		$v1 = $pos1->getIntermediateWithXValue($pos2, $this->minX);
		$v2 = $pos1->getIntermediateWithXValue($pos2, $this->maxX);
		$v3 = $pos1->getIntermediateWithYValue($pos2, $this->minY);
		$v4 = $pos1->getIntermediateWithYValue($pos2, $this->maxY);
		$v5 = $pos1->getIntermediateWithZValue($pos2, $this->minZ);
		$v6 = $pos1->getIntermediateWithZValue($pos2, $this->maxZ);

		if ($v1 !== null and !$this->isVectorInYZ($v1)) {
			$v1 = null;
		}

		if ($v2 !== null and !$this->isVectorInYZ($v2)) {
			$v2 = null;
		}

		if ($v3 !== null and !$this->isVectorInXZ($v3)) {
			$v3 = null;
		}

		if ($v4 !== null and !$this->isVectorInXZ($v4)) {
			$v4 = null;
		}

		if ($v5 !== null and !$this->isVectorInXY($v5)) {
			$v5 = null;
		}

		if ($v6 !== null and !$this->isVectorInXY($v6)) {
			$v6 = null;
		}

		$vector = null;
		$distance = PHP_INT_MAX;

		foreach ([$v1, $v2, $v3, $v4, $v5, $v6] as $v) {
			if ($v !== null and ($d = $pos1->distanceSquared($v)) < $distance) {
				$vector = $v;
				$distance = $d;
			}
		}

		if ($vector === null) {
			return null;
		}

		$f = -1;

		if ($vector === $v1) {
			$f = Facing::WEST;
		} elseif ($vector === $v2) {
			$f = Facing::EAST;
		} elseif ($vector === $v3) {
			$f = Facing::DOWN;
		} elseif ($vector === $v4) {
			$f = Facing::UP;
		} elseif ($vector === $v5) {
			$f = Facing::NORTH;
		} elseif ($vector === $v6) {
			$f = Facing::SOUTH;
		}

		return new RayTraceResult($this, $f, $vector);
	}

	public function isVectorInYZ(Vector3 $vector): bool {
		return $vector->y >= $this->minY and $vector->y <= $this->maxY and $vector->z >= $this->minZ and $vector->z <= $this->maxZ;
	}

	public function isVectorInXZ(Vector3 $vector): bool {
		return $vector->x >= $this->minX and $vector->x <= $this->maxX and $vector->z >= $this->minZ and $vector->z <= $this->maxZ;
	}

	public function isVectorInXY(Vector3 $vector): bool {
		return $vector->x >= $this->minX and $vector->x <= $this->maxX and $vector->y >= $this->minY and $vector->y <= $this->maxY;
	}
}