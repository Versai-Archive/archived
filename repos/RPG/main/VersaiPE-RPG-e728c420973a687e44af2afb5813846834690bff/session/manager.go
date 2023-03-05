package session

import (
	"github.com/df-mc/dragonfly/server/player"
)

var sessions = map[string]Session{}

// New will add a session to the manager, and automatically pull
// from the database, and put there information in the session,
// If no data was found, it will but the base information
func New(player *player.Player) *Session {
	session := Session{
		Player:      player,
		DisplayName: player.Name(),
		CurrentPet:  Pet{},
		Mana:        325,
		Pets:        []Pet{},
		Skills: Skills{
			Combat: LevelData{
				Level:     5,
				XpCurrent: 3463,
				XpNeeded:  5000,
				Progress:  0.51,
			},
			Farming: LevelData{
				Level:     5,
				XpCurrent: 3463,
				XpNeeded:  5000,
				Progress:  0.51,
			},
			Lumberjack: LevelData{
				Level:     5,
				XpCurrent: 3463,
				XpNeeded:  5000,
				Progress:  0.51,
			},
			Mining: LevelData{
				Level:     5,
				XpCurrent: 3463,
				XpNeeded:  5000,
				Progress:  0.51,
			},
		},
		Stats: Stats{
			Health:      100,
			Defense:     0,
			MaxMana:     500,
			Strength:    32,
			Speed:       553,
			CritChance:  32,
			CritDamage:  33252,
			MiningSpeed: 100,
			Damage:      10,
		},
	}
	sessions[player.XUID()] = session
	session.Scoreboard()
	return &session
}

func UnRegister(session *Session) {
	delete(sessions, session.Player.XUID())
}
