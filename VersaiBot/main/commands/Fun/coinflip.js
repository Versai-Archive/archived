const Discord = require("discord.js");
const got = require("got");
const emojis = require("../../storage/emojis.json");

class coinflip {
  constructor() {
    this.name = "coinflip";
    this.aliases = ["cf", "flip"];
    this.description = "Flip a coin!";
    this.usage = "coinflip"
    this.category = "Fun";
  }

  async onRun(client, msg, args) {
    const n = Math.floor(Math.random() * 2);
    let result;
    if (n === 1) result = "Heads";
    else result = "Tails";

    const embed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.coinflip} **${msg.member.displayName} The coin landed on ${result}**!`
      );

    msg.channel.send(embed);
    console.log(
      "\x1b[35m",
      `$[INFO]: Command coinflip executed by ${msg.author.tag}`
    );
  }
}

module.exports = coinflip;
