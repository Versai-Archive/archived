package worldedit

import (
	"github.com/VersaiPE/BuildServer/session"
	"github.com/df-mc/dragonfly/server/cmd"
	"github.com/df-mc/dragonfly/server/player"
	"github.com/go-gl/mathgl/mgl64"
)

type PasteCommand struct{}

func (cc PasteCommand) Run(source cmd.Source, _ *cmd.Output) {
	_player := source.(*player.Player)

	ses := session.Get(_player)

	invalidPoint := mgl64.Vec3{0, 0, 0}

	if ses.Selection().Pos1 == invalidPoint {
		_player.Message("Please select a position 1!")
		return
	}

	if ses.Selection().Pos2 == invalidPoint {
		_player.Message("Please select a position 2!")
		return
	}

	if ses.Selection().World != nil {
		res := ses.Clipboard().Paste(_player)
		if res.Error != "" {
			_player.Message(res.Error)
		}
		_player.Messagef("§aPasted §e%v §ablocks in §e%v", len(ses.Clipboard().Blocks), res.Time)
	}
}
