<?php

/*
 * Auto-generated by libasynql-def
 * Created from mysql.sql
 */

declare(strict_types=1);

namespace ARTulloss\VersaiSettings;

interface Queries{
	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/Redo 3/plugins/BetterPerms/resources/mysql.sql:49
	 *
	 * <h3>Variables</h3>
	 * - <code>:permission</code> string, required in mysql.sql
	 * - <code>:username</code> string, required in mysql.sql
	 */
	public const DELETE_PLAYER_PERMISSIONS = "delete.player_permissions";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/Redo 3/plugins/BetterPerms/resources/mysql.sql:9
	 */
	public const INIT_PLAYER_PERMISSIONS = "init.player_permissions";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/Redo 3/plugins/BetterPerms/resources/mysql.sql:6
	 */
	public const INIT_PLAYERS = "init.players";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/Redo 3/plugins/BetterPerms/resources/mysql.sql:18
	 *
	 * <h3>Variables</h3>
	 * - <code>:usergroup</code> string, required in mysql.sql
	 * - <code>:username</code> string, required in mysql.sql
	 * - <code>:oldgroup</code> string, optional in mysql.sql
	 * - <code>:until</code> int, required in mysql.sql
	 */
	public const INSERT_PLAYER = "insert.player";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/Redo 3/plugins/BetterPerms/resources/mysql.sql:23
	 *
	 * <h3>Variables</h3>
	 * - <code>:permission</code> string, required in mysql.sql
	 * - <code>:username</code> string, required in mysql.sql
	 */
	public const INSERT_PLAYER_PERMISSIONS = "insert.player_permissions";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/Redo 3/plugins/BetterPerms/resources/mysql.sql:29
	 *
	 * <h3>Variables</h3>
	 * - <code>:username</code> string, required in mysql.sql
	 */
	public const SELECT_PLAYER = "select.player";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/Redo 3/plugins/BetterPerms/resources/mysql.sql:33
	 *
	 * <h3>Variables</h3>
	 * - <code>:username</code> string, required in mysql.sql
	 */
	public const SELECT_PLAYER_PERMISSIONS = "select.player_permissions";

	/**
	 * <h4>Declared in:</h4>
	 * - C:/Users/Adam/Desktop/stuff/pocketmine/Redo 3/plugins/BetterPerms/resources/mysql.sql:42
	 *
	 * <h3>Variables</h3>
	 * - <code>:usergroup</code> string, required in mysql.sql
	 * - <code>:username</code> string, required in mysql.sql
	 * - <code>:oldgroup</code> string, optional in mysql.sql
	 * - <code>:until</code> int, required in mysql.sql
	 */
	public const UPDATE_PLAYER = "update.player";

}