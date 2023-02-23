package worldedit

import (
	"github.com/VersaiPE/BuildServer/mathutils"
	"github.com/VersaiPE/BuildServer/pallete"
	"github.com/VersaiPE/BuildServer/session"
	"github.com/VersaiPE/BuildServer/utils"
	"github.com/df-mc/dragonfly/server/block/cube"
	"github.com/df-mc/dragonfly/server/cmd"
	"github.com/df-mc/dragonfly/server/player"
	"github.com/df-mc/dragonfly/server/world"
	"github.com/go-gl/mathgl/mgl64"
	"math/rand"
	"strconv"
	"strings"
	"time"
)

type FillCommand struct {
	Blocks string `cmd:"blocks"`
}

func (fc FillCommand) Run(source cmd.Source, _ *cmd.Output) {

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

		// stone:5,dirt,sand
		_blockNames := strings.Split(fc.Blocks, ",")

		var _blocks []string

		for _, d := range _blockNames {
			if strings.Contains(d, ":") {
				dat := strings.Split(d, ":")

				num, e := strconv.Atoi(dat[1])

				if e != nil {
					_player.Message("The weight of a block must be a number!")
				}

				for i := 0; i <= num; i++ {
					_blocks = append(_blocks, dat[0])
				}
			} else {
				_blocks = append(_blocks, d)
			}
		}

		// Verify all blocks work
		for _, block := range _blocks {
			if _, found := pallete.Blocks[block]; found {
				continue
			} else {
				_player.Message("Block could not be found!")
				return
			}
		}

		rand.Seed(time.Now().Unix())

		changes := map[mgl64.Vec3]world.Block{}

		for y := minY; y <= maxY; y++ {
			for x := minX; x <= maxX; x++ {
				for z := minZ; z <= maxZ; z++ {
					point := mgl64.Vec3{float64(x), float64(y), float64(z)}
					var _block string

					if len(_blocks) == 0 {
						_block = fc.Blocks
					} else {
						_block = _blocks[rand.Intn(len(_blocks))]
					}
					// save all the changes beging made
					// use this to get the previous block that was there
					prevBlock := selection.World.Block(cube.PosFromVec3(point))
					// add the previous block to a slice, to send off to a ReverseDataHolder
					changes[point] = prevBlock
					// actually set the block
					selection.World.SetBlock(cube.PosFromVec3(point), pallete.Blocks[_block], nil)
				}
			}
		}

		// Convert array to a ReverseBlockStorage
		reverse := utils.BlockStorageHolder{
			Blocks: changes,
			World:  selection.World,
		}
		// save the undo
		data := _session.ReverseData().SaveUndo(reverse)
		_session.SetReverseData(data)
	}
}
