package session

type Skills struct {
	Combat     LevelData `bson:"combat"`
	Farming    LevelData `bson:"farming"`
	Lumberjack LevelData `bson:"lumberjack"`
	Mining     LevelData `bson:"mining"`
}
