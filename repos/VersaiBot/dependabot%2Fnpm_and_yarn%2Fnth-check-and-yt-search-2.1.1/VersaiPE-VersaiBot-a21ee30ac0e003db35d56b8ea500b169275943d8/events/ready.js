module.exports = (client) => {  

  const mobileStatusIndicator = require('discord.js/src/util/Constants.js');
  mobileStatusIndicator.DefaultOptions.ws.properties.$browser = 'Discord Android';

  const statusList = [
    { msg: "outside (JK who does that?)", type: "PLAYING" },
    { msg: "alone :'(", type: "PLAYING" },
    { msg: "with your heart </3", type: "PLAYING" },
    { msg: `with over ${client.users.cache.size} users!`, type: "PLAYING" },
    { msg: "who even reads these anyways?", type: "PLAYING" },
    { msg: "the haters hate", type: "WATCHING" },
    { msg: "you (turn around)", type: "WATCHING" },
    { msg: "grass grow", type: "WATCHING" },
    { msg: "funny cat videos", type: "WATCHING" },
    { msg: "Déjà vu Watching Déjà vu Watching Déjà vu Watching Déjà vu", type: "WATCHING" },
    { msg: "the world crumble", type: "WATCHING" },
    { msg: "over you from above 👼", type: "WATCHING" },
    { msg: "your conversations", type: "LISTENING" },
  ];

  setInterval(async () => {
    const index = Math.floor(Math.random() * statusList.length + 1) - 1;
    await client.user.setActivity(statusList[index].msg, {
      type: statusList[index].type,
    });
  }, 60000);
    
console.clear();
console.log('\n\x1b[33m%s\x1b[0m', `$[Event]: Attempting to login...`);
console.log('\n\x1b[32m%s\x1b[0m', `$[INFO]: Logged in as ${client.user.tag} successfully!`);
}