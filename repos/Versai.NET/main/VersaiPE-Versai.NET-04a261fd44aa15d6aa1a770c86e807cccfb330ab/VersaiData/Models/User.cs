using MongoDB.Bson;
using MongoDB.Bson.Serialization.Attributes;

namespace VersaiData.Models; 

public class User {
    
    [BsonId]
    [BsonRepresentation(BsonType.String)]
    public string xuid { get; set; }
    
    public int rank { get; set; }

    public string joined { get; set; }
    
}

public class SMPUser : User {
    
    [BsonId]
    [BsonRepresentation(BsonType.String)]
    public new string xuid { get; set; }
    
    public string Class { get; set; }
    
    public int MaxMana { get; set; }
    
    public int Defence { get; set; }
    
    public float Agility { get; set; }
    
    public int Coins { get; set; }
    
    public int QuestId { get; set; }

    public dynamic QuestProgress { get; set; }
    
    public int MaxHealth { get; set; }

    public int MiningLevel { get; set; }

    public float MiningXp { get; set; }

    public int WoodcuttingLevel { get; set; }

    public float WoodcuttingXp { get; set; }

    public int FarmingLevel { get; set; }

    public float FarmingXp { get; set; }

    public int FishingLevel { get; set; }

    public float FishingXp { get; set; }

    public int CombatLevel { get; set; }

    public float CombatXp { get; set; }
    
}