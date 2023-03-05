const Discord = require("discord.js");
const moment = require("moment");
const config = require("../../storage/config.json");
const emojis = require("../../storage/emojis.json");

class botinfo {
  constructor() {
    this.name = "botinfo";
    this.aliases = [];
    this.description = "Get information about this bot";
    this.usage = ["botinfo"];
    this.category = "Miscellaneous";
  }

  async onRun(client, msg, args) {
    // const duration = moment.duration(client.uptime).format(" D [days], H [hrs], m [mins], s [secs]");
    function duration(ms) {
      const sec = Math.floor((ms / 1000) % 60).toString();
      const min = Math.floor((ms / (1000 * 60)) % 60).toString();
      const hrs = Math.floor((ms / (1000 * 60 * 60)) % 60).toString();
      const days = Math.floor((ms / (1000 * 60 * 60 * 24)) % 60).toString();
      return `${days.padStart(1, "0")} days, ${hrs.padStart(
        2,
        "0"
      )} hours, ${min.padStart(2, "0")} minutes, ${sec.padStart(
        2,
        "0"
      )} seconds `;
    }

    const embed = new Discord.MessageEmbed()
      .setTitle("Versai BotInfo! 😊")
      .setThumbnail(client.user.displayAvatarURL())
      .setDescription(
        `• Mem Usage: \`${(
          process.memoryUsage().heapUsed /
          1024 /
          1024
        ).toFixed(2)}\`\n• Websocket: \`${Math.round(
          client.ws.ping
        )}ms\`\n• Users: \`${client.users.cache.size}\`\n• Servers: \`${
          client.guilds.cache.size
        }\`\n• Discord.js: \`v${Discord.version}\`\n• Node: \`${
          process.version
        }\`\n• Uptime: \`${duration(client.uptime)}\`\n`
      )
      .setTimestamp()
      .setFooter("Made <3 by diverse#6858")
      .setColor("#2F3136");
    msg.channel.send(embed);
    console.log(
      "\x1b[35m",
      `$[INFO]: Command botstats executed by ${msg.author.tag}`
    );
  }
}

module.exports = botinfo;
