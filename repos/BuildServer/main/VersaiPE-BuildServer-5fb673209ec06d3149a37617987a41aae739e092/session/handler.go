package session

import (
	"github.com/df-mc/dragonfly/server/block/cube"
	"github.com/df-mc/dragonfly/server/event"
	"github.com/df-mc/dragonfly/server/item"
	"github.com/df-mc/dragonfly/server/player"
	"github.com/go-gl/mathgl/mgl64"
	"github.com/sandertv/gophertunnel/minecraft/text"
)

type Handler struct {
	player.NopHandler
	p *player.Player
}

func NewHandler(p *player.Player) *Handler {
	return &Handler{p: p}
}

// Position 1
func (handler *Handler) HandleBlockBreak(ctx *event.Context, pos cube.Pos, _ *[]item.Stack, _ *int) {
	heldItem, _ := handler.p.HeldItems()

	if _, ok := heldItem.Item().(item.Axe); ok {
		if heldItem.CustomName() == "Builders Wand" {
			session := Get(handler.p)

			session.SetPointOne(pos.Vec3())
			session.SetSelectionWorld(handler.p.World())

			handler.p.SendTip(text.Colourf("<green>Set Position 1 to <grey>%v</grey>", pos))
			ctx.Cancel()
		}
	}
}

// Position 2
func (handler *Handler) HandleItemUseOnBlock(ctx *event.Context, pos cube.Pos, _ cube.Face, clickPos mgl64.Vec3) {
	heldItem, _ := handler.p.HeldItems()

	if _, ok := heldItem.Item().(item.Axe); ok {
		if heldItem.CustomName() == "Builders Wand" {
			session := Get(handler.p)

			session.SetPointTwo(pos.Vec3())
			session.SetSelectionWorld(handler.p.World())

			handler.p.SendTip(text.Colourf("<green>Set Position 2 to <grey>%v</grey>", pos))
		}
	}
}
