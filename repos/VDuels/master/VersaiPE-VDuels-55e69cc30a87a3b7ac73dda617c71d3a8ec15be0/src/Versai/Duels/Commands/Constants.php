<?php
declare(strict_types=1);

namespace Versai\Duels\Commands;

use pocketmine\utils\TextFormat;
use jojoe77777\FormAPI\SimpleForm;

class Constants{
	// All

	public const BACK = 'Back';
	public const BACK_IMAGE = 'textures/ui/listx';
	public const BACK_TYPE = SimpleForm::IMAGE_TYPE_PATH;

	// Party

	public const PARTY_LEADER_FORMAT = TextFormat::GOLD . '» ' . '{player}';

	public const JOINED_PARTY = TextFormat::GREEN . "{player} joined the party!";
	public const JOINED_PARTY_SELF = TextFormat::GREEN . 'You joined {leader}\'s party!';

	public const LEFT_PARTY = TextFormat::RED . "{player} left the party!";
	public const KICKED_FROM_PARTY = TextFormat::RED . 'You were kicked from the party!';
	public const LEFT_PARTY_SELF = TextFormat::RED . 'You left your party!';

	public const LEFT_PARTY_OWNER_SELF = TextFormat::RED . 'You left your party, causing it to be deleted';
	public const LEFT_PARTY_OWNER = TextFormat::RED . 'Your party was disbanded because the owner left!';
	public const KICK_SELF = TextFormat::RED . 'You can\'t kick yourself!';

	public const NO_PARTIES_OPEN = TextFormat::RED . 'No parties are open!';
	public const NOT_IN_PARTY_SELF = TextFormat::RED . 'You\'re not in a party!';
	public const NOT_IN_YOUR_PARTY = TextFormat::RED . 'Player is not in your party!';
	public const PARTY_NOT_EXIST = TextFormat::RED . 'That party doesn\'t exist';

	public const ALREADY_IN_PARTY = TextFormat::RED . 'You\'re already in a party! Do /party leave to leave!';
	public const ALREADY_IN_YOUR_PARTY = TextFormat::RED . 'You\'re already in that party! Do /party leave to leave!';
	public const PLAYER_ALREADY_IN_PARTY = TextFormat::RED . 'That player is already in a your party!';

	public const CREATE_PARTY = TextFormat::GREEN . 'You created a party, you can send your friends the code {code} to join or use /party invite!';
	public const DISBAND_PARTY_SELF = TextFormat::GREEN . 'Successfully disbanded your party!';
	public const DISBAND_PARTY = TextFormat::RED . 'Your party was disbanded';

	public const NOW_STATE = TextFormat::GREEN . 'Your party is now {state}';
	public const ALREADY_STATE = TextFormat::GREEN . 'Your party is already {state}';

	public const ACTION_SELF = TextFormat::RED . 'You can\'t {action} yourself!';

	public const PROMOTION = TextFormat::GREEN . '{player} was promoted to party leader!';

	public const MUST_ENTER = TextFormat::RED . 'You have to enter a {type}!';
	public const MUST_BE_LEADER = TextFormat::RED . 'You need to be the party leader to do that!';

	public const NONE_IN_MATCH = TextFormat::RED . 'None of your party members are in a match!';

	public const SPECTATE_FINISHED = TextFormat::RED . 'That match is already over so you can\'t spectate it!';
	public const SPECTATE_ALREADY = TextFormat::RED . 'You are already spectating that match!';

	public const LEADER_ENDED_MATCH = TextFormat::BLUE . 'The party leader stopped the match early!';
	public const LEADER_ENDED_MATCH_SELF = TextFormat::BLUE . 'You stopped the match early!';

	public const YOU_ARE_ONLY_PLAYER = TextFormat::RED . 'You are the only player';

	public const GLITCH = TextFormat::RED . 'There was a glitch!';

	public const PARTY_CHAT_SYMBOL = '#';
	public const PARTY_CHAT_FORMAT = TextFormat::BLUE . 'Party | {player} » {msg}';
	public const PARTY_CHAT_FORMAT_LEADER = TextFormat::GOLD . 'Party | {player} » {msg}';

	// Duel

	public const DUELS_INFO = TextFormat::BLUE . 'Duels version {version} by ARTulloss!';

	public const DUEL_CREATE_ARENA_SUCCESS = 'Successfully created arena {arena} on {level}';

	public const DUEL_CREATE_ARENA_FAIL = TextFormat::RED . 'Failed to create arena.';

	public const DUEL_REMOVE_ARENA_SUCCESS = TextFormat::GREEN . 'Successfully removed arena {arena} on {level}';

	public const DUEL_REMOVE_ARENA_FAIL = TextFormat::RED . '{level} isn\'t registered as an arena';

	public const NO_PENDING_DUEL_REQUESTS = TextFormat::RED . "You have no pending duel requests!";

	public const QUEUE_BUTTON_FORMAT =  'Playing: {playing} Queued: {queued}';

}