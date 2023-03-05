package session

import (
	"github.com/google/uuid"
)

type Pet struct {
	UUID   uuid.UUID `bson:"uuid"`
	Type   string    `bson:"type"`
	Exp    uint      `bson:"exp"`
	Active bool      `bson:"active"`
	Tier   string    `bson:"tier"`
	Level  LevelData `bson:"level"`
	Name   string    `bson:"name"`
	// "stat" => 325
	Stats map[string]int `bson:"stats"`
	Lore  string         `bson:"lore"`
}
