<?php


namespace Martin\SkyBlock\constants;


interface Queries{

	public const INIT_PLAYERS = "skyblock.init.players";
	public const INIT_ISLANDS = "skyblock.init.islands";

	public const GET_PLAYER_BY_USERNAME = "skyblock.get.player.by_name";
	public const GET_PLAYER_BY_ID = "skyblock.get.player.by_id";
	public const GET_PLAYER_ALL = "skyblock.get.player.all";

	public const CREATE_PLAYER = "skyblock.create.player";
}