<?php

namespace ethaniccc\VAC\data\click;

final class ClickData {

	public int $delta = 0;
	public int $lastClickTick = 0;
	public int $cps = 0;
	public array $clicks = [];

	public function add(int $currentTick): void {
		$delta = $currentTick - $this->lastClickTick;
		$this->lastClickTick = $currentTick;
		$this->delta = $delta;
		$this->clicks[] = $currentTick;
		$this->clicks = array_filter($this->clicks, function (int $tick) use ($currentTick): bool {
			return $currentTick - $tick <= 20;
		});
		$this->cps = count($this->clicks);
	}

}