package worldedit

import (
	"github.com/VersaiPE/BuildServer/mathutils"
	"github.com/VersaiPE/BuildServer/session"
	"github.com/df-mc/dragonfly/server/cmd"
	"github.com/df-mc/dragonfly/server/player"
)

type PosOneCommand struct{}

type PosTwoCommand struct{}

func (pc PosOneCommand) Run(source cmd.Source, _ *cmd.Output) {
	p := source.(*player.Player)

	ses := session.Get(p)

	ses.SetPointOne(mathutils.FloorVec3(p.Position()))
}

func (pc PosTwoCommand) Run(source cmd.Source, _ *cmd.Output) {
	p := source.(*player.Player)

	ses := session.Get(p)

	ses.SetPointTwo(mathutils.FloorVec3(p.Position()))
}
