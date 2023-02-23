<?php
declare(strict_types=1);

namespace Versai\Disguise;

use pocketmine\entity\Skin;

class Disguise
{

    /**
     * @param string $name
     * @param Skin|null $skin
     */
	public function __construct(private string $name, private ?Skin $skin = null){}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return Skin|null
	 */
	public function getSkin(): ?Skin
	{
		return $this->skin;
	}
}