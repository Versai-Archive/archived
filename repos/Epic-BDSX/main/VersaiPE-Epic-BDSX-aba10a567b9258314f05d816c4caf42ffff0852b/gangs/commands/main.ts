const system = server.registerSystem(0, 0);

let dummyG = [{
    Name: '-', //Gang Name
    guildID: 0, //Gang ID
    subtitle: '', //subtitle
    level: 0, //Gang level
    xp: 0, //Gang XP
    xpM: 0,
    PMC: 0, 
    o: 'open', //Gang status
    r: 10 //rank
}]

let dummyP1 = [{
   Name: '-', //username
   xuid: '', //xuid
   guildID: 0, //ID
   perm: '' //ranking and command perms
}]

let localfile = "Gangs.json";
let localfilePlayer = "GangPlayer.json";

let guildJs:any[] = [];
let dataJs:any[] = [];
let inviteJs:any[] = [];

//Had to look things up

let cooldown = new Map<string, number>();
let c = setInterval(()=>{
    cooldown.forEach((v, k)=>{
        v -= 1
        if (v > 0) cooldown.set(k, v);
        if (v <= 0) cooldown.delete(k);
    });
}, 1000);
open(localfile,'a+',function(err:any,fd:any){
    if(err) throw err;
    try {
        JSON.parse(readFileSync(localfile, "utf8"));
    } catch (err) {
        writeFileSync(localfile, JSON.stringify(dummyG), "utf8")
    }
    guildJs = JSON.parse(readFileSync(localfile, "utf8"));
});
open(localfilePlayer,'a+',function(err:any,fd:any){
    if(err) throw err;
    try {
        JSON.parse(readFileSync(localfilePlayer, "utf8"));
    } catch (err) {
        writeFileSync(localfilePlayer, JSON.stringify(dummyPl), "utf8")
    }
    dataJs = JSON.parse(readFileSync(localfilePlayer, "utf8"));
});
                  
command.register('gang invite', 'invite a player to your gang', CommandPermissionLevel.Normal).overload((params, origin, output) => {
  
  }, {});
