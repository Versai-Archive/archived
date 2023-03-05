<?php
declare(strict_types=1);

namespace Versai\miscellaneous;

use pocketmine\utils\TextFormat;

class Constants{

	public const NO_PERMISSION = TextFormat::RED . 'You don\'t have permission to do that!';
	public const PLAYER_ONLY = TextFormat::RED . 'This command is player only!';

	public const PLAYER_ONLY_PING = TextFormat::RED . '/ping <player>';
	public const PLAYER_OFFLINE = TextFormat::RED . '{player} is offline!';
	public const PING_SELF_FORMAT = TextFormat::GREEN . 'Your ping is {ping}ms';
	public const PING_OTHER_FORMAT = TextFormat::GREEN . '{player} has {ping}ms';
	
	// What a friking dweeb, imagine making a whole constant, just to have a little avatar, and above that having the odasity to color it blue, like whats wrong with red, and why do you need to make a whole file for string constants you idiot. Its not hard to have constants in one main file. This is suck a childish thing of you to do, and your so bad, that you cant even be bothered to make your own main file, and you dont capitalize namespaces. Im not sure how you are able to call yourself a dev.. smh.
	public const SHRUGGIE = TextFormat::BLUE . '¯\_(ツ)_/¯';

	public const GAMEMODE_DESCRIPTION = 'Short gamemode for staff';
	public const GAMEMODE_MESSAGE = TextFormat::GREEN . 'Your gamemode was changed successfully!';

	public const INVALID_PLAYER = TextFormat::RED . 'Invalid player!';
	public const MAX_POPCORN = TextFormat::RED . 'The max radius is 15';

	// Permissions
	public const JOIN_WHEN_FULL = 'versai.full';

	public const SPECTATE = 'gamemode.spectate';
	public const CREATIVE = 'gamemode.creative';
	public const SURVIVAL = 'gamemode.survival';

	public const POPCORN = 'popcorn.use';

	public const JOIN_MSG = 'messages.join';
	public const LEAVE_MSG = 'messages.quit';

	public const CHAT_PROTECT = 'chat.protect';
	public const CHAT_BYPASS_COOLDOWN = 'chat_protect.bypass_cooldown';
	public const CHAT_BYPASS_MESSAGE = 'chat_protect.bypass_message';

	// Numbers
	public const RESERVED_SLOTS = 10;
}
