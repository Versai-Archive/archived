<?php


namespace Martin\GameAPI\Utils;


use Martin\GameAPI\Game\Team\Team;
use Martin\GameAPI\Types\ColorTeamType;
use pocketmine\utils\TextFormat;

class TeamUtils
{
    public static function getRedTeam(int $minimumPlayers, int $maximumPlayers): Team
    {
        return new Team(ColorTeamType::TEAM_RED, "Red", TextFormat::RED, $minimumPlayers, $maximumPlayers);
    }

    public static function getBlueTeam(int $minimumPlayers, int $maximumPlayers): Team
    {
        return new Team(ColorTeamType::TEAM_BLUE, "Red", TextFormat::BLUE, $minimumPlayers, $maximumPlayers);
    }

    public static function getGreenTeam(int $minimumPlayers, int $maximumPlayers): Team
    {
        return new Team(ColorTeamType::TEAM_GREEN, "Red", TextFormat::GREEN, $minimumPlayers, $maximumPlayers);
    }

    public static function getYellowTeam(int $minimumPlayers, int $maximumPlayers): Team
    {
        return new Team(ColorTeamType::TEAM_YELLOW, "Red", TextFormat::YELLOW, $minimumPlayers, $maximumPlayers);
    }
}