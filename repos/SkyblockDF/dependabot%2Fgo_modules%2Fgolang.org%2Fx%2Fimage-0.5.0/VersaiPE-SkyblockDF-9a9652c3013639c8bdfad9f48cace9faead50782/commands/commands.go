package commands

import (
	"github.com/df-mc/dragonfly/server/cmd"
	"skyblock/commands/island"
)

func RegisterCommands() {
	cmd.Register(cmd.New("island", "Basic island command", []string{"is"}, island.Island{}))
}
