package handlers

import (
	"fmt"
	"github.com/df-mc/dragonfly/server/event"
	"github.com/df-mc/dragonfly/server/player"
	"github.com/df-mc/dragonfly/server/player/chat"
	"skyblock/session"
)

type PlayerHandler struct {
	player.NopHandler
	Player  *player.Player
	Session *session.Session
}

func (handler PlayerHandler) HandleChat(ctx *event.Context, message *string) {
	ctx.Cancel()
	_, _ = fmt.Fprintf(chat.Global, "§3Player %v §7> %v", handler.Player.Name(), *message)
}
