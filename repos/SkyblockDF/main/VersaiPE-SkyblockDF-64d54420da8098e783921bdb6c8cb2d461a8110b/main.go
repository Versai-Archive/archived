package main

import (
	"skyblock/commands"
	"skyblock/utils"
)

func main() {
	commands.RegisterCommands()
	utils.StartServer()
}
