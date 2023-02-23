<?php

/***
 *    ___                                          _
 *   / __\___  _ __ ___  _ __ ___   __ _ _ __   __| | ___
 *  / /  / _ \| '_ ` _ \| '_ ` _ \ / _` | '_ \ / _` |/ _ \
 * / /__| (_) | | | | | | | | | | | (_| | | | | (_| | (_) |
 * \____/\___/|_| |_| |_|_| |_| |_|\__,_|_| |_|\__,_|\___/
 *
 * Commando - A Command Framework virion for PocketMine-MP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Written by @Versai\vTempRanks\libs\CortexPE <https://Versai\vTempRanks\libs\CortexPE.xyz>
 *
 */
declare(strict_types=1);

namespace Versai\vTempRanks\libs\CortexPE\Commando\traits;


use pocketmine\command\CommandSender;
use Versai\vTempRanks\libs\CortexPE\Commando\args\BaseArgument;

interface IArgumentable {
	public function generateUsageMessage(): string;
	public function hasArguments(): bool;

	/**
	 * @return BaseArgument[][]
	 */
	public function getArgumentList(): array;
	public function parseArguments(array $rawArgs, CommandSender $sender): array;
	public function registerArgument(int $position, BaseArgument $argument): void;
}