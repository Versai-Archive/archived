//rules channel <#708292981064466463>

const Discord = require("discord.js");
const emojis = require("../../storage/emojis.json");

class rules {
  constructor() {
    this.name = "rules";
    this.aliases = ["serverrules", "server-rules"];
    this.description = "Get in-game/Discord server rules";
    this.usage = ["rules"];
    this.category = "Server";
  }

  async onRun(client, msg, args) {
    let embed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.cheers} You can refer to the rules in <#708292981064466463> !`
      );

    msg.channel.send(embed);
    console.log(
      "\x1b[35m",
      `$[INFO]: Command rules executed by ${msg.author.tag}`
    );
  }
}

module.exports = rules;
