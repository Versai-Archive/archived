package worldedit

import (
	"github.com/VersaiPE/BuildServer/mathutils"
	"github.com/VersaiPE/BuildServer/pallete"
	"github.com/VersaiPE/BuildServer/session"
	"github.com/df-mc/dragonfly/server/block/cube"
	"github.com/df-mc/dragonfly/server/cmd"
	"github.com/df-mc/dragonfly/server/player"
	"github.com/go-gl/mathgl/mgl64"
	"math/rand"
	"strings"
	"time"
)

type ReplaceCommand struct {
	Replace     string `cmd:"replacing"`
	ReplaceWith string `cmd:"replace_with"`
}

func (rc ReplaceCommand) Run(source cmd.Source, output *cmd.Output) {

	_player := source.(*player.Player)

	_session := session.Get(_player)

	selection := _session.Selection()

	invalidPoint := mgl64.Vec3{0, 0, 0}

	if selection.Pos1 == invalidPoint {
		_player.Message("Please select a position 1!")
		return
	}

	if selection.Pos2 == invalidPoint {
		_player.Message("Please select a position 2!")
		return
	}

	if selection.World != nil {
		minX, maxX := mathutils.MinMax(selection.Pos1.X(), selection.Pos2.X())
		minY, maxY := mathutils.MinMax(selection.Pos1.Y(), selection.Pos2.Y())
		minZ, maxZ := mathutils.MinMax(selection.Pos1.Z(), selection.Pos2.Z())

		_blocks := strings.Split(rc.ReplaceWith, ",")

		for _, block := range _blocks {
			if _, found := pallete.Blocks[block]; found {
				continue
			} else {
				_player.Message("Block could not be found!")
				return
			}
		}

		// check if the block being replaced is a block
		if _, good := pallete.Blocks[rc.Replace]; !good {
			_player.Message("Block could not be found!")
			return
		}

		rand.Seed(time.Now().Unix())

		for y := minY; y <= maxY; y++ {
			for x := minX; x <= maxX; x++ {
				for z := minZ; z <= maxZ; z++ {
					point := mgl64.Vec3{float64(x), float64(y), float64(z)}
					currentBlock := selection.World.Block(cube.PosFromVec3(point))
					_block := _blocks[rand.Intn(len(_blocks))]
					if _, _replace := pallete.Blocks[rc.Replace]; !_replace {
						_player.Message("Block could not be found!")
						return
					}
					if currentBlock == pallete.Blocks[rc.Replace] {
						selection.World.SetBlock(cube.PosFromVec3(point), pallete.Blocks[_block], nil)
					}
				}
			}
		}
	}
}
