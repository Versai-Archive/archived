<?php
declare(strict_types=1);

namespace Duo\vcosmetics\constants;

use pocketmine\utils\TextFormat;

class Messages {

	public const No_Permission = TextFormat::BLUE . "Groups v1 by ARTulloss";
	public const Unknown_Argument = TextFormat::RED . "Unknown argument!";


	public const Tag_Added = TextFormat::GREEN . 'Successfully added tag "' . TextFormat::RESET . '{tag}' . TextFormat::GREEN . '" with format ' . TextFormat::RESET . '{format}';
	public const Tag_Exists = TextFormat::YELLOW . "Tag already exists... overwriting!";
	public const Tag_Not_Exists = TextFormat::RED . "Tag doesn't exist!";
	public const Tag_Deleted = TextFormat::GREEN . "Tag deleted successfully";
	public const Tag_Unique = TextFormat::GREEN . "Tag name and format cannot be the same!";
	public const No_Tags = TextFormat::RED . "There are no tags in the configuration";
	public const Tags_Are = TextFormat::BLUE . "The tags in the configuration are: ";
	public const Tag_Custom_Invalid = TextFormat::RED . "Invalid custom tag name!";
	public const Tag_Updated = TextFormat::GREEN . "Tags updated!";
	public const Tags_Too_Many = TextFormat::RED . "That's too many tags!";
	public const Tag_Saved = TextFormat::GREEN . 'Tags successfully saved!';

	public const Consent_Content = "§9Welcome to Versai!§r Our purpose is to create a fun and fair server to play! Some quick rules, 1. Don't use anything that gives you an unfair advantage over other players. 2. Respect staff and try to be polite, swearing is allowed, but keep it minimal! 3. Have fun, we're here for you, if there is anything we can do to make you happier with our server, we'll try our best! By pressing accept, you're agreeing to the rules above and acknowledging that we have the right to prohibit you from playing in the event that you don't comply. In addition, please understand that we have to collect some data in order to provide stats for you.";
	public const Consent_Not = "§cYou have to accept the rules in order to play!";
	public const Consent_Title = "    × Versai Network ×";
	public const Consent_Agree = "Agree";
	public const Consent_Disagree = "§cLeave";

	public const Not_Found_Player = TextFormat::RED . "Player not found!";
	public const Not_Found_Group = TextFormat::RED . "Group doesn't exist!";
	public const Set_Group = TextFormat::GREEN . "Successfully set {player}'s group to {group}";
	public const Give_Permission_Success = TextFormat::GREEN . "Permission successfully set!";
	public const Give_Permission_Fail = TextFormat::RED . "Player already has that permission!";
	public const Remove_Permission_Success = TextFormat::GREEN . "Successfully removed permission!";
	public const Remove_Permission_Fail = TextFormat::RED . "The player didn't have that permission!";


	public const Settings_Updated = TextFormat::BLUE . "[Versai] §rSettings updated!";
	public const Clan_Tag_Permissions = TextFormat::RED . 'You have to purchase {tag} clan tag to use it';
	public const Tag_Permissions = TextFormat::RED . "You have to purchase {tag} to use it";
	public const Tag_Custom_Permission = TextFormat::RED . "You have to purchase the custom tag from the store to use this!";
	public const Cape_Permission = TextFormat::RED . "You have to purchase a cape or have a rank to use other capes!";
	public const Flight_Permission = TextFormat::RED . "You have to purchase Ultra or Elite rank to use this!";
	public const Particles_Permission = TextFormat::RED . "You have to purchase Ultra or Elite rank to use this!";

}