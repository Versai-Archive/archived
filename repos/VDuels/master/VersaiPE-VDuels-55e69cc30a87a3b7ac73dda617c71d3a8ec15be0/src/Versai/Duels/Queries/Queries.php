<?php
declare(strict_types=1);

namespace Versai\Duels\Queries;

interface Queries{

	public const INIT_PLAYER_ELO = "init.player_elo";
	public const INIT_PLAYERS = "init.players";

	public const INSERT_ELO = "insert.elo";
	public const INSERT_PLAYER = "insert.player";

	public const SELECT_ALL_ELO = "select.all_elo";
	public const SELECT_ELO = "select.elo";
	public const SELECT_ID = "select.id";
	public const SELECT_PLAYER = "select.player";
	public const SELECT_TOP = "select.top";

}
