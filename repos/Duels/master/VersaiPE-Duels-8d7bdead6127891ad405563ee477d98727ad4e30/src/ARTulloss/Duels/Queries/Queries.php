<?php
declare(strict_types=1);

namespace ARTulloss\Duels\Queries;

interface Queries{
	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/mysql.sql:18
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/sqlite.sql:18
	 */
	public const INIT_PLAYER_ELO = "init.player_elo";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/mysql.sql:9
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/sqlite.sql:9
	 */
	public const INIT_PLAYERS = "init.players";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/mysql.sql:58
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/sqlite.sql:58
	 *
	 * <h3>Variables</h3>
	 * - <code>:player_name</code> string, required in mysql.sql, sqlite.sql
	 * - <code>:kit</code> string, required in mysql.sql, sqlite.sql
	 * - <code>:elo</code> int, required in mysql.sql, sqlite.sql
	 */
	public const INSERT_ELO = "insert.elo";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/mysql.sql:50
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/sqlite.sql:50
	 *
	 * <h3>Variables</h3>
	 * - <code>:player_name</code> string, required in mysql.sql, sqlite.sql
	 */
	public const INSERT_PLAYER = "insert.player";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/mysql.sql:42
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/sqlite.sql:42
	 *
	 * <h3>Variables</h3>
	 * - <code>:player_name</code> string, required in mysql.sql, sqlite.sql
	 */
	public const SELECT_ALL_ELO = "select.all_elo";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/mysql.sql:38
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/sqlite.sql:38
	 *
	 * <h3>Variables</h3>
	 * - <code>:player_name</code> string, required in mysql.sql, sqlite.sql
	 * - <code>:kit</code> string, required in mysql.sql, sqlite.sql
	 */
	public const SELECT_ELO = "select.elo";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/mysql.sql:28
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/sqlite.sql:28
	 *
	 * <h3>Variables</h3>
	 * - <code>:id</code> int, required in mysql.sql, sqlite.sql
	 */
	public const SELECT_ID = "select.id";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/mysql.sql:24
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/sqlite.sql:24
	 *
	 * <h3>Variables</h3>
	 * - <code>:player_name</code> string, required in mysql.sql, sqlite.sql
	 */
	public const SELECT_PLAYER = "select.player";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/mysql.sql:33
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/PocketMine-MP-3.5.0/plugins/Duels/resources/sqlite.sql:33
	 *
	 * <h3>Variables</h3>
	 * - <code>:amount</code> int, required in mysql.sql, sqlite.sql
	 * - <code>:kit</code> string, required in mysql.sql, sqlite.sql
	 */
	public const SELECT_TOP = "select.top";

}
