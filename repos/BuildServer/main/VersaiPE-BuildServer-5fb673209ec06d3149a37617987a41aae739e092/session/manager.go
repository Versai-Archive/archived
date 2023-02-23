package session

import "github.com/df-mc/dragonfly/server/player"

var sessions = map[string]*Session{}

func New(p *player.Player) *Session {
	ses := &Session{
		Player: p,
	}
	sessions[p.Name()] = ses
	ses.SendScoreboard()
	return ses
}

func Get(p *player.Player) *Session {
	if session, ok := sessions[p.Name()]; ok {
		return session
	}

	return nil
}
