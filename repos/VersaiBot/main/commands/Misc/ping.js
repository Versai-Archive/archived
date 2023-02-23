const Discord = require("discord.js");

class ping {
  constructor() {
    this.name = "ping";
    this.aliases = ["pong", "ms", "latency"];
    this.description = "Get Client/API ping";
    this.usage = ["ping"];
    this.category = "Miscellaneous";
  }

  async onRun(client, msg, args) {
    const message = await msg.channel.send(`🏓 Pinging....`);
    let pembed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setTitle("🏓 Pong!")
      .setThumbnail(msg.guild.iconURL())
      .addField("**Latency**", `\`${Date.now() - msg.createdTimestamp}ms\``)
      .addField("**API Latency**", `\`${Math.round(client.ws.ping)}ms\``)
      .setTimestamp()
      .setFooter(`${msg.author.username}`, msg.author.avatarURL());

    message.edit(" ", pembed);
    console.log(
      "\x1b[35m",
      `$[INFO]: Command ping executed by ${msg.author.tag}`
    );
  }
}

module.exports = ping;
