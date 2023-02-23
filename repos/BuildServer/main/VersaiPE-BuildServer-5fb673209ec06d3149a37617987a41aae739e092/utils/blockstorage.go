package utils

import (
	"github.com/df-mc/dragonfly/server/world"
	"github.com/go-gl/mathgl/mgl64"
)

type BlockStorageHolder struct {
	Blocks map[mgl64.Vec3]world.Block
	World  *world.World
}
