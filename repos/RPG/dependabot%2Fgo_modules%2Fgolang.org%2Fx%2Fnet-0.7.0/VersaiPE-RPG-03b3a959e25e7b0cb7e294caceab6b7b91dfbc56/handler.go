package main

import (
	"RPG/data"
	"RPG/session"
	"fmt"
	"github.com/df-mc/dragonfly/server/event"
	"github.com/df-mc/dragonfly/server/player"
	"github.com/df-mc/dragonfly/server/player/chat"
)

type PlayerHandler struct {
	player.NopHandler
	Player  *player.Player
	Session *session.Session
}

func (h PlayerHandler) HandleChat(ctx *event.Context, message *string) {
	ctx.Cancel()
	_, _ = fmt.Fprintf(chat.Global, "§3Player %v §7> %v", h.Player.Name(), *message)
}

func (h PlayerHandler) HandleQuit() {
	err := data.SaveSession(h.Session)
	session.UnRegister(h.Session)
	if err != nil {
		panic(err)
	}
}
