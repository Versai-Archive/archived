<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/4/2019
 * Time: 1:16 PM
 */
declare(strict_types=1);
namespace ARTulloss\Protector\Constants;

use pocketmine\utils\TextFormat;

/**
 * Class Constants
 * @package ARTulloss\Protector\Constants
 */
class Constants
{
	// VPN

	public const VPN_MESSAGE = TextFormat::RED . "Unfortunately, VPN's and proxies are blocked!\nError? Tweet us @VersaiPE";

	public const VPN_REQUEST_LIMIT = TextFormat::RED . '{key} has reached the rate limit!';

	public const RESET_IPS = TextFormat::GREEN . 'Successfully reset all stored IPs';

	public const ALLOW_IP = TextFormat::GREEN . 'Successfully allowed IP {ip}';

	public const BLOCK_IP = TextFormat::GREEN . 'Successfully blocked IP {ip}';

	public const NOT_AN_IP = TextFormat::RED . 'That\'s not an IP!';

	public const VPN_COMMAND_DESCRIPTION = 'Antivpn command';

	public const VPN_COMMAND_USAGE = '/vpn <reset> | <allow> | <block>';

	public const VPN_COMMAND_PERMISSION = 'protector.vpn';

	public const BLOCK = [1];

	// Commands

	public const NO_PERMISSION = TextFormat::RED . 'You don\'t have permission to use that!';

	public const NO_DATA_EXISTS = TextFormat::RED . 'Player has no data!';

	// Player Info

	public const PLAYER_INFO_PERMISSION = 'protector.pinfo';

	public const PLAYER_INFO_USAGE = '/pinfo <player>';

	public const PLAYER_INFO_DESCRIPTION = 'Find information regarding a player';

	// Alias

	public const ALIAS_PERMISSION = 'protector.alias';

	public const ALIAS_USAGE = '/alias <player> <i|c|d>';

	public const ALIAS_DESCRIPTION = 'Who\'s that player!';

	public const ALIAS_TITLE_FORMAT = 'Alias for {player}';

	public const POSSIBLE_PLAYER_FORMAT = '{player} with ' . TextFormat::RED . '{matches} | {possible} ' . TextFormat::RESET . ' matches';

	public const PLAYER_NOT_EXIST = TextFormat::RED . 'Player doesn\'t exist!';

}