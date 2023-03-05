package session

import (
	"github.com/VersaiPE/BuildServer/mathutils"
	"github.com/VersaiPE/BuildServer/utils"
	"github.com/df-mc/dragonfly/server/block/cube"
	"github.com/df-mc/dragonfly/server/player"
	"github.com/df-mc/dragonfly/server/world"
	"github.com/go-gl/mathgl/mgl64"
	"math"
	"time"
)

type Clipboard struct {
	Blocks    map[mgl64.Vec3]world.Block
	Relative  mgl64.Vec3
	World     *world.World
	Direction *cube.Direction
}

type PasteResult struct {
	Error string
	Time  time.Duration
}

func (c Clipboard) SetBlocks(blocks map[mgl64.Vec3]world.Block) {
	c.Blocks = blocks
}

func (c Clipboard) Reset() {
	c.Blocks = nil
	c.World = nil
	c.Relative = mgl64.Vec3{0, 0, 0}
}

func (c Clipboard) Copy(PosOne mgl64.Vec3, PosTwo mgl64.Vec3, Relative mgl64.Vec3, Wrold *world.World) Clipboard {
	minX, maxX := mathutils.MinMax(PosOne.X(), PosTwo.X())
	minY, maxY := mathutils.MinMax(PosOne.Y(), PosTwo.Y())
	minZ, maxZ := mathutils.MinMax(PosOne.Z(), PosTwo.Z())

	_map := map[mgl64.Vec3]world.Block{}

	for y := minY; y <= maxY; y++ {
		for x := minX; x <= maxX; x++ {
			for z := minZ; z <= maxZ; z++ {
				point := mgl64.Vec3{float64(x), float64(y), float64(z)}

				_block := c.World.Block(cube.PosFromVec3(point))

				_map[point] = _block
			}
		}
	}

	c.Blocks = _map
	c.Relative = Relative
	c.World = Wrold
	return c
}

func (c Clipboard) Paste(p *player.Player) PasteResult {
	if c.Empty() {
		return PasteResult{
			Error: "Nothing found in clipboard",
			Time:  time.Millisecond,
		}
	}

	start := time.Now()

	changes := map[mgl64.Vec3]world.Block{}

	if c.World != nil {
		for pos, _block := range c.Blocks {

			//xMin, xMax := mathutils.MinMax(pos.X(), p.Position().X())
			//yMin, yMax := mathutils.MinMax(pos.Y(), p.Position().Y())
			//zMin, zMax := mathutils.MinMax(pos.Z(), p.Position().Z())
			//
			//blockPosition := mgl64.Vec3{float64(xMax - xMin), float64(yMax - yMin), float64(zMax - zMin)}

			motion := p.Position().Add(mgl64.Vec3{0.5, 0, 0.5}).Sub(c.Relative)

			floorX, floorY, floorZ := math.Floor(motion.X()), math.Floor(motion.Y()), math.Floor(motion.Z())

			//fmt.Println(mgl64.Vec3{floorX + motion.X(), floorY + motion.Y(), floorZ + motion.Z()})

			blockPosition := mgl64.Vec3{floorX + pos.X(), floorY + pos.Y(), floorZ + pos.Z()}

			// The block before it is changed from paste
			prev := c.World.Block(cube.PosFromVec3(blockPosition))
			// Add the block to the changes slice
			changes[blockPosition] = prev
			// Set the block
			c.World.SetBlock(cube.PosFromVec3(blockPosition), _block, nil)
		}

		// Apply the changes to the players session
		ses := Get(p)

		data := ses.ReverseData().SaveUndo(utils.BlockStorageHolder{
			Blocks: changes,
			World:  c.World,
		})

		ses.SetReverseData(data)

	}

	return PasteResult{Time: time.Now().Sub(start)}
}

func (c Clipboard) Empty() bool {
	return c.Blocks == nil
}

func (c Clipboard) Rotate(NewDirection *cube.Direction) {
	c.Direction = NewDirection
}
