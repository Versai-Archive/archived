package session

import "github.com/df-mc/dragonfly/server/player"

var sessions = map[string]Session{}

func New(player *player.Player) *Session {
	session := Session{
		Player: player,
	}
	sessions[player.XUID()] = session
	session.Scoreboard()
	return &session
}

func Get(player *player.Player) Session {
	return sessions[player.XUID()]
}

func UnRegister(session Session) {
	delete(sessions, session.Player.XUID())
}
