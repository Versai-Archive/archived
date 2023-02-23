package main

import (
	"github.com/VersaiPE/BuildServer/commands"
	"github.com/VersaiPE/BuildServer/serverutil"
)

func main() {
	commands.RegisterAll()
	serverutil.StartServer()
}
