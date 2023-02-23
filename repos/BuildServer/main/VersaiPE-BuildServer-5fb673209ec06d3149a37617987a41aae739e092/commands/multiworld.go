package commands

import (
	"github.com/VersaiPE/BuildServer/utils"
	"github.com/df-mc/dragonfly/server/cmd"
	"github.com/df-mc/dragonfly/server/player"
)

type MultiWorldTeleport struct {
	Teleport cmd.SubCommand `cmd:"teleport"`
	World    string         `cmd:"world"`
}

type MultiWorldCreate struct {
	Create cmd.SubCommand `cmd:"create"`
	Name   string         `cmd:"name"`
	//Generator string         `cmd:"generator"`
}

type MultiWorldList struct {
	List cmd.SubCommand `cmd:"list"`
}

type MultiWorldInfo struct {
	Info cmd.SubCommand `cmd:"info"`
}

// Run ...
func (mwtp MultiWorldTeleport) Run(s cmd.Source, o *cmd.Output) {
	p := s.(*player.Player)
	worlds := utils.WorldManager.AllWorlds()
	for _, w := range worlds {
		if w == mwtp.World {
			err := utils.WorldManager.LoadWorld(mwtp.World, mwtp.World)
			if err != nil {
				p.Message("An error occured while teleporting you to the world")
				return
			}
			world, _ := utils.WorldManager.World(mwtp.World)
			world.AddEntity(p)
		}
	}
	p.Messagef("Could not find world %v", mwtp.World)
	/*world, found := utils.WorldManager.World(mwtp.World)
	if !found {
		p.Messagef("Could not find world %v", mwtp.World)
	}
	world.AddEntity(p)*/
}

// Run ...
func (c MultiWorldCreate) Run(s cmd.Source, o *cmd.Output) {
	p := s.(*player.Player)
	p.ShowCoordinates()
	err := utils.WorldManager.LoadWorld(c.Name, c.Name)
	if err != nil {
		p.Messagef("Failed to create world %v", c.Name)
		return
	}
	p.Messagef("Created new world %v", c.Name)
}

// Run ...
func (l MultiWorldList) Run(s cmd.Source, o *cmd.Output) {
	p := s.(*player.Player)
	var message = ""
	for _, world := range utils.WorldManager.AllWorlds() {
		message += world + "\n"
	}
	p.Message(message)
}

func (i MultiWorldInfo) Run(s cmd.Source, o *cmd.Output) {
	p := s.(*player.Player)
	world, ok := utils.WorldManager.World(p.World().Name())
	if !ok {
		p.Messagef("Could not find world that you are in \"%v\"", p.World().Name())
		return
	}
	// spawn := "§7Spawn: (§a" + string(rune(world.Spawn().X())) + "§7, §a" + string(rune(world.Spawn().Y())) + "§7, §a" + string(rune(world.Spawn().Z()))
	p.Messagef("§7World info for world %v \n §7Spawn: (§a%v§7, §a%v§7, §a%v§7)", p.World().Name(), world.Spawn().X(), world.Spawn().Y(), world.Spawn().Z())
}
