package session

import (
	"github.com/df-mc/dragonfly/server/player"
	"github.com/df-mc/dragonfly/server/player/scoreboard"
	"time"
)

type LevelData struct {
	Level     int
	XpCurrent float64
	XpNeeded  float64
	Progress  float64
}

type Session struct {
	Player      *player.Player
	DisplayName string
	CurrentPet  Pet
	Mana        int
	MaxMana     int
	Pets        []Pet
	Skills      Skills
	Stats       Stats
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
	s.Player.SendScoreboard(sb)
}
