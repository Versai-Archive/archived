const Discord = require("discord.js");

class uptime {
  constructor() {
    this.name = "uptime";
    this.aliases = ["up", "onlinetime", "online-time", "ot"];
    this.description = "Get the Bot's uptime";
    this.usage = ["uptime"];
    this.category = "Miscellaneous";
  }

  async onRun(client, msg, args) {
    let totalSeconds = client.uptime / 1000;
    let days = Math.floor(totalSeconds / 86400);
    totalSeconds %= 86400;
    let hours = Math.floor(totalSeconds / 3600);
    totalSeconds %= 3600;
    let minutes = Math.floor(totalSeconds / 60);
    let seconds = Math.floor(totalSeconds % 60);

    const embed = new Discord.MessageEmbed()
      .setTitle(`I have been online for:`)
      .setColor("#2F3136")
      .setDescription(
        `${days} days ${hours} hours ${minutes} minutes ${seconds} seconds`
      );

    msg.channel.send(embed);
    console.log(
      "\x1b[35m",
      `$[INFO]: Command Uptime executed by ${msg.author.tag}`
    );
  }
}

module.exports = uptime;
