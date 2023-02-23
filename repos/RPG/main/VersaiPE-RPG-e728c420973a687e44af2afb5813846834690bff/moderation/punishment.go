package moderation

import "time"

type Punishment struct {
	Staff      string    `bson:"staff"`
	Reason     string    `bson:"reason"`
	Start      time.Time `bson:"start"`
	Permanent  bool      `bson:"permanent"`
	Expiration time.Time `bson:"expiration"`
}
