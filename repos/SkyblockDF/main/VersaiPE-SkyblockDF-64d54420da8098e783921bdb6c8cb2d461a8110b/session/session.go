package session

import (
	"github.com/df-mc/dragonfly/server/player"
	"github.com/df-mc/dragonfly/server/player/scoreboard"
	"time"
)

type Session struct {
	Player *player.Player
}

func (s *Session) Scoreboard() {
	go func() {
		for {
			if s == nil || s.Player == nil {
				return
			}
			s.defaultScoreboard()
			time.Sleep(1 * time.Second)
		}
	}()
}

func (s *Session) defaultScoreboard() {
	sb := scoreboard.New("§3Versai §7Skyblock")
	_, _ = sb.WriteString("§3Island: §8Monke")
	s.Player.SendScoreboard(sb)
}
