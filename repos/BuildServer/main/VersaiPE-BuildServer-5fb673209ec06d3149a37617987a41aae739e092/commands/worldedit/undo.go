package worldedit

import (
	"github.com/VersaiPE/BuildServer/session"
	"github.com/df-mc/dragonfly/server/block/cube"
	"github.com/df-mc/dragonfly/server/cmd"
	"github.com/df-mc/dragonfly/server/player"
)

type UndoCommand struct{}

func (uc UndoCommand) Run(source cmd.Source, _ *cmd.Output) {

	p := source.(*player.Player)

	ses := session.Get(p)

	if ok, undo := ses.ReverseData().NextUndo(); ok {
		for pos, block := range undo.Blocks {
			undo.World.SetBlock(cube.PosFromVec3(pos), block, nil)
		}
	} else {
		p.Message("Nothing found to undo!")
		return
	}

}
