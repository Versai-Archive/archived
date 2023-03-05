<?php

namespace Versai\OneBlock\OneBlock;

interface OneBlockPermissions {

	public const BREAK_BLOCKS = "oneblock.break_blocks";

	public const BREAK_ONE_BLOCK = "oneblock.break_one_block";

	public const PLACE_BLOCKS = "oneblock.place_blocks";

	public const OPEN_CHESTS = "oneblock.open_chests";

	public const INVITE_MEMBERS = "oneblock.invite_members";

	public const ADD_MEMBER = "oneblock.add_members";

	public const REMOVE_MEMBERS = "oneblock.remove_members";

	public const KICK_PLAYERS = "oneblock.kick_players";

	public const BAN_PLAYERS = "oneblock.ban_players";

	public const LOCK_ISLAND = "oneblock.lock_island";

	public const UNLOCK_ISLAND = "oneblock.unlock_island";

	public const SET_ISLAND_TYPE = "oneblock.set_type";

	public const BANNED = "oneblock.banned";

	public const DAMAGE_ENTITIES = "oneblock.damage_entities";

	public const FLY = "oneblock.can_fly";

	public const INTERACT = "oneblock.can_interact";

	public const PICKUP_ITEMS = "oneblock.items_pickup";
	
	public const DROP_ITEM = "oneblock.items_drop";

}