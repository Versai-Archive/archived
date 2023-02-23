namespace VersaiData.Models; 

public class DatabaseSettings {
    
    public string ConnectionString { get; set; }
    
    public string DatabaseName { get; set; }
    
    public string CollectionName { get; set; }
    
}

public class UserDatabaseSettings : DatabaseSettings {}

public class UserSmpDatabaseSettings : DatabaseSettings {}