package commands

import (
	"github.com/VersaiPE/BuildServer/commands/worldedit"
	"github.com/df-mc/dragonfly/server/cmd"
)

func RegisterAll() {
	cmd.Register(cmd.New("multiworld", "multiworld commands", []string{"multiworld", "mw"},
		MultiWorldTeleport{},
		MultiWorldCreate{},
		MultiWorldList{},
		MultiWorldInfo{},
	))

	// World Edit

	cmd.Register(cmd.New("/wand", "Get the builders wand", []string{}, worldedit.Wand{}))
	cmd.Register(cmd.New("/fill", "fill a area with blocks", []string{"/set"}, worldedit.FillCommand{}))
	cmd.Register(cmd.New("/replace", "Replace blocks", []string{}, worldedit.ReplaceCommand{}))
	cmd.Register(cmd.New("/copy", "copy your selected blocks", []string{}, worldedit.CopyCommand{}))
	cmd.Register(cmd.New("/paste", "paste your selected blocks", []string{}, worldedit.PasteCommand{}))
	cmd.Register(cmd.New("/1", "Select position 1", []string{"/pos1"}, worldedit.PosOneCommand{}))
	cmd.Register(cmd.New("/2", "Select position 2", []string{"/pos2"}, worldedit.PosTwoCommand{}))
	//cmd.Register(cmd.New("/sphere", "Create a sphere", []string{}, worldedit.SphereCommand{}))
	cmd.Register(cmd.New("/undo", "undo a previous action", []string{}, worldedit.UndoCommand{}))
	cmd.Register(cmd.New("/arc", "Create a arch from 2 points", []string{}, worldedit.ArcCommand{}))
}
