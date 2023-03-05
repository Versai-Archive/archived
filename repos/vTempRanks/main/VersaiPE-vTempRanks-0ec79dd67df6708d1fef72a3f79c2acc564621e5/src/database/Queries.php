<?php

namespace Versai\vTempRanks\database;

interface Queries {

    public const INIT_TABLE = "temp_ranks.init.table";

    public const SELECT_PLAYER = "temp_ranks.select.player";

    public const INSERT_PLAYER = "temp_ranks.insert.player.temp_rank";

    public const RESET_RANK = "temp_ranks.reset.rank";
}