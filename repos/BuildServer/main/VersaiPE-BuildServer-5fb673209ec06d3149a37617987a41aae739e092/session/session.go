package session

import (
	"github.com/VersaiPE/BuildServer/mathutils"
	"github.com/df-mc/dragonfly/server/player"
	"github.com/df-mc/dragonfly/server/player/scoreboard"
	"github.com/df-mc/dragonfly/server/world"
	"github.com/df-mc/dragonfly/server/world/particle"
	"github.com/go-gl/mathgl/mgl64"
	"time"
)

type Session struct {
	Player *player.Player
}

var (
	selections Selection
	clipboard  Clipboard
	reverse    ReverseDataHolder
)

func (s *Session) SendScoreboard() {
	go func() {
		scb := scoreboard.New("§7Versai §3Build")
		//hand, _ := s.Player.HeldItems()
		//var item string
		//if name := hand.Item(); name == nil {
		//	item, _ = name.EncodeItem()
		//} else {
		//	item = "air"
		//}
		//_, err := scb.WriteString("")
		//_, _ = scb.WriteString("§9Held Item§7: " + item)
		//if err != nil {
		//	return
		//}
		_, e := scb.WriteString("§9World§7: " + s.Player.World().Name())
		if e != nil {
			return
		}
		scb.RemovePadding()
		s.Player.SendScoreboard(scb)
		time.Sleep(time.Second * 1)
	}()
}

func (s *Session) StartParticles() {
	go func() {
		for {
			invalidPoint := mgl64.Vec3{0, 0, 0}
			if s.Selection().Pos1 == invalidPoint {
				continue
			}

			if s.Selection().Pos2 == invalidPoint {
				continue
			}

			minX, maxX := mathutils.MinMax(selections.Pos1.X(), selections.Pos2.X())
			minY, maxY := mathutils.MinMax(selections.Pos1.Y(), selections.Pos2.Y())
			minZ, maxZ := mathutils.MinMax(selections.Pos1.Z(), selections.Pos2.Z())

			for y := minY; y <= maxY; y++ {
				for x := minX; x <= maxX; x++ {
					for z := minZ; z <= maxZ; z++ {
						if minX < x && x < maxX {
							continue
						}
						if minZ < z && z < maxZ {
							continue
						}
						s.Player.ShowParticle(mgl64.Vec3{float64(x), float64(y), float64(z)}, particle.Evaporate{ /*Colour: colornames.Yellow*/ })
					}
				}
			}
			time.Sleep(time.Second * 1)
		}
	}()
}

func (s *Session) SetPointOne(point mgl64.Vec3) {
	selections.Pos1 = point
}

func (s *Session) SetPointTwo(point mgl64.Vec3) {
	selections.Pos2 = point
}

func (s *Session) SetSelectionWorld(w *world.World) {
	selections.World = w
}

func (s *Session) Selection() Selection {
	return selections
}

func (s *Session) Clipboard() Clipboard {
	return clipboard
}

func (s *Session) SetClipboard(c Clipboard) {
	clipboard = c
}

func (s *Session) ReverseData() ReverseDataHolder {
	return reverse
}

// i feel like a idiot
func (s *Session) SetReverseData(holder ReverseDataHolder) {
	reverse = holder
}
