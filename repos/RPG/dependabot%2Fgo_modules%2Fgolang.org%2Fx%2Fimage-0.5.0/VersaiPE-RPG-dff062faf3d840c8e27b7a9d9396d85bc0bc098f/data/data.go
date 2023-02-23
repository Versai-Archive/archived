package data

import (
	"RPG/moderation"
	"RPG/session"
	"context"
	"go.mongodb.org/mongo-driver/mongo"
	"go.mongodb.org/mongo-driver/mongo/options"
	"go.mongodb.org/mongo-driver/mongo/readpref"
	"time"
)

type UserData struct {
	XUID        string         `bson:"XUID"`
	Name        string         `bson:"Name"`
	Address     string         `bson:"Address"`
	Whitelisted bool           `bson:"Whitelisted"`
	FirstLogin  time.Time      `bson:"FirstLogin"`
	PlayTime    time.Duration  `bson:"PlayTime"`
	CurrentPet  session.Pet    `bson:"CurrentPet"`
	Pets        []session.Pet  `bson:"Pets"`
	Skills      session.Skills `bson:"Skills"`
	Stats       session.Stats  `bson:"Stats"`
}

type PunishmentData struct {
	Mute moderation.Punishment `bson:"mute"`
	Ban  moderation.Punishment `bson:"ban"`
}

var coll *mongo.Collection

const uri = "mongodb://localhost:27017"

func init() {
	ctx, cancel := context.WithTimeout(context.Background(), time.Second*10)
	defer cancel()

	client, err := mongo.Connect(ctx, options.Client().ApplyURI(uri))
	if err != nil {
		panic(err)
	}

	coll = client.Database("RPG").Collection("users")

	if err = client.Ping(ctx, readpref.Primary()); err != nil {
		panic(err)
	}
}
