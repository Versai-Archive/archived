using Microsoft.Extensions.Options;
using MongoDB.Driver;
using VersaiData.Models;

namespace VersaiData.Services; 

public class UserService {

    private readonly IMongoCollection<User> _usersCollection;
    
    public UserService(IOptions<UserDatabaseSettings> settings) {

        var client = new MongoClient(settings.Value.ConnectionString);
        var database = client.GetDatabase(settings.Value.DatabaseName);
        
        _usersCollection = database.GetCollection<User>(settings.Value.CollectionName);
    }
    
    /*
     * Get all users in a list
     */
    public async Task<List<User>> GetAll() =>
        await _usersCollection.Find(user => true).ToListAsync();

    /*
     * Get a user by xuid
     */
    public async Task<User?> Get(string xuid) =>
        await _usersCollection.Find<User>(user => user.xuid == xuid).FirstOrDefaultAsync();
    
    /*
     * Create a new user
     */
    public async Task Create(User user) =>
        await _usersCollection.InsertOneAsync(user);
    
    /*
     * Update a user by xuid
     */
    public async Task Update(User user) =>
        await _usersCollection.ReplaceOneAsync(x => x.xuid == user.xuid, user);
}

public class SmpUserService {

    private readonly IMongoCollection<SMPUser> _usersCollection;
    
    public SmpUserService(IOptions<UserSmpDatabaseSettings> settings) {
        var client = new MongoClient(settings.Value.ConnectionString);
        var database = client.GetDatabase(settings.Value.DatabaseName);
        
        _usersCollection = database.GetCollection<SMPUser>(settings.Value.CollectionName);
    }
    
    /*
     * * Get all users in a list
     */
    public async Task<List<SMPUser>> GetAll() =>
        await _usersCollection.Find(user => true).ToListAsync();

    /*
     * Get a user by xuid
     */
    public async Task<SMPUser?> Get(string xuid) =>
        await _usersCollection.Find<SMPUser>(user => user.xuid == xuid).FirstOrDefaultAsync();
    
    /*
     * Create a new user
     */
    public async Task Create(SMPUser user) =>
        await _usersCollection.InsertOneAsync(user);
    
    /*
     * Update a user by xuid
     */
    public async Task Update(SMPUser user) =>
        await _usersCollection.ReplaceOneAsync(x => x.xuid == user.xuid, user);
}