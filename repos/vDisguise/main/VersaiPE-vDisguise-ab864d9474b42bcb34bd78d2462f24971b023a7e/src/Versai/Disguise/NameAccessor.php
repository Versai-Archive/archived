<?php
declare(strict_types=1);

namespace Versai\Disguise;

use function array_rand;
use function str_replace;

class NameAccessor
{
	/** @var string[] */
	private array $names = [];

	/**
	 * @param string[] $names
	 */
	public function setNames(array $names): void
	{
	    foreach ($names as $key => $name) {
	        $names[$key] = str_replace('_', ' ', $name); // _ isn't valid in xbox live names, but is on mcpc
        }
		$this->names = $names;
	}

	/**
	 * @return null|string
	 */
	public function getUniqueName(): ?string
	{
		if($this->names === [])
            return null;
		$key = array_rand($this->names);
		$name = $this->names[$key];
		unset($this->names[$key]);
		return $name;
	}

	/**
	 * @return string[]
	 */
	public function getNames(): array
	{
		return $this->names;
	}
}