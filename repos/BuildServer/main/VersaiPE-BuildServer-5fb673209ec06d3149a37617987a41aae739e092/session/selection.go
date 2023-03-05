package session

import (
	"github.com/df-mc/dragonfly/server/world"
	"github.com/go-gl/mathgl/mgl64"
)

type Selection struct {
	World *world.World
	Pos1  mgl64.Vec3
	Pos2  mgl64.Vec3
}
