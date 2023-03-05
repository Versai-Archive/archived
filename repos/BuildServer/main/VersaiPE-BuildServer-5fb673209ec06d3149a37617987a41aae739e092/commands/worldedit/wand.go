package worldedit

import (
	"github.com/df-mc/dragonfly/server/cmd"
	"github.com/df-mc/dragonfly/server/item"
	"github.com/df-mc/dragonfly/server/player"
)

type Wand struct{}

func (w Wand) Run(source cmd.Source, output *cmd.Output) {
	wand := item.NewStack(item.Axe{
		Tier: item.ToolTierWood,
	}, 1)
	wand = wand.WithCustomName("Builders Wand").WithLore("Left click to set position 1", "Right click to set position 2")

	p := source.(*player.Player)

	_, err := p.Inventory().AddItem(wand)
	if err != nil {
		return
	}
}
