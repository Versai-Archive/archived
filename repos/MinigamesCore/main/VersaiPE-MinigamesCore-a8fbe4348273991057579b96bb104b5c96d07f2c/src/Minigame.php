<?php

declare(strict_types=1);

namespace Versai;

/** Implement this into the Main class of your minigame plugin then just register it */
interface Minigame {

	public function __construct(string $name);

	public function getName(): string;

	/** The description of the minigame */
	public function getInfo(): string;

	public function getVersion(): string;

}