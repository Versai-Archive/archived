package worldedit

import (
	"github.com/VersaiPE/BuildServer/session"
	"github.com/df-mc/dragonfly/server/cmd"
	"github.com/df-mc/dragonfly/server/player"
	"github.com/go-gl/mathgl/mgl64"
	"math"
	"time"
)

type CopyCommand struct{}

func (cc CopyCommand) Run(source cmd.Source, _ *cmd.Output) {
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
		start := time.Now()
		cb := ses.Clipboard().Copy(ses.Selection().Pos1, ses.Selection().Pos2, mgl64.Vec3{math.Floor(_player.Position().X()), math.Floor(_player.Position().Y()), math.Floor(_player.Position().Z())}, _player.World())
		_player.Messagef("§aCopied §e%v §ablocks in §e%v", len(cb.Blocks), time.Now().Sub(start))
		ses.SetClipboard(cb)
	}
}
