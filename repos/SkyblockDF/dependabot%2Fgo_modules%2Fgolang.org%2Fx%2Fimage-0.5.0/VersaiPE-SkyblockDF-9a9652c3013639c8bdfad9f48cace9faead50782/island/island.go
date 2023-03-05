package island

import "github.com/df-mc/dragonfly/server/player"

type Island struct {
	Owner       *player.Player
	Name        string
	Description string
	Level       int8
	Xp          int32
}
