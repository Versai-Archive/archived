package session

type Stats struct {
	Health      uint16 `bson:"health"`
	Defense     uint32 `bson:"defense"`
	MaxMana     uint32 `bson:"maxMana"`
	Strength    uint32 `bson:"strength"`
	Speed       uint16 `bson:"speed"`
	CritChance  uint16 `bson:"critChance"`
	CritDamage  uint64 `bson:"critDamage"`
	MiningSpeed uint64 `bson:"miningSpeed"`
	Damage      uint64 `bson:"damage"`
}
