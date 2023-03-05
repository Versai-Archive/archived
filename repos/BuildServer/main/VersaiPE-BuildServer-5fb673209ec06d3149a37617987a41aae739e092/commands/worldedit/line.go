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
	"math"
	"strconv"
	"strings"
	"time"
)

type LineCommand struct {
	Pallete string `cmd:"pallete"`
}

func (lc LineCommand) Run(source cmd.Source, _ *cmd.Output) {
	p := source.(*player.Player)
	
	var positions []mgl.Vector3{}

	session := sessions.Get(p)

    pos1 := session.Selection().Pos1
    pos2 := session.Selection().Pos2

    delX = pos2.X() - pos1.X();
    delY = pos2.Y() - pos1.Y();	
    delZ = pos2.Z() - pos1.Z();

	steps := math.Max(math.Abs(delX), math.Abs(delY), math.Abs(delZ));

    for i := 0; i < steps; i++ {
        newX = pos1.X() + delX / (points - 1) * i;
        newY = pos2.Y() + delY / (points - 1) * i;
        newZ = pos1.Z() + delZ / (points - 1) * i;

        positions = append(positions, mgl.Vector3{newX, newY, newZ});
    }

    world := session.Selection().World

	_blockNames := strings.Split(lc.Pallete, ",")

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

	var _block string
	changes := map[mgl64.Vec3]world.Block{}

    for _, pos := range positions {
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