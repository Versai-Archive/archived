<?php
declare(strict_types=1);

namespace Duo\vcosmetics\constants;

class Permissions {

	public const PREFIX = "cosmetics.";

	public const GROUP = self::PREFIX . "group";
	public const TAG_COMMAND = self::PREFIX . "tag";
	public const TAGS = self::PREFIX . "tag." . "{tag}";
	public const CLAN_TAGS = self::PREFIX . "tag.clan." . "{tag}";
	public const CUSTOM_TAG = self::PREFIX . "custom.tag";
	public const FLIGHT = self::PREFIX . "flight";
	public const CAPES = self::PREFIX . "cape." . "{cape}";
	public const PARTICLES_HIT = self::PREFIX . "particles.hit." . "{particle}";
	public const PARTICLES_FOLLOW = self::PREFIX . "particles.follow." . "{particle}";

}