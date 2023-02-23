const Discord = require("discord.js");
const config = require("../../storage/config.json");
const emojis = require("../../storage/emojis.json");

class join {
  constructor() {
    this.name = "join";
    this.aliases = ["connect"];
    this.description = "Connects the bot to a voice channel";
    this.usage = ["join"];
    this.category = "Music";
  }

  async onRun(client, msg, args) {

    const noPermsUserEmbed = new Discord.MessageEmbed()
    .setColor("#2F3136")
    .setDescription(`${emojis.crossmark} You don't have permission to use this command.`);
    const notInVC = new Discord.MessageEmbed()
    .setColor("#2F3136")
    .setDescription(`${emojis.crossmark} You are not in a voice channel.`);
    const noPermsBotEmbed = new Discord.MessageEmbed()
    .setColor("#2F3136")
    .setDescription(`${emojis.crossmark} I do not have permission to join this channel.`);

    if (!msg.member.permissions.has("MOVE_MEMBERS")) return msg.channel.send(noPermsUserEmbed);
    if (!msg.member.voice.channelID) return msg.channel.send(notInVC);
    
    let vc = msg.guild.channels.cache.get(msg.member.voice.channelID);
    if (!vc.permissionsFor(client.user.id).has("CONNECT")) return msg.channel.send(noPermsBotEmbed);

    vc.join().catch(e => console.log(e));
    console.log(
        "\x1b[35m",
        `$[INFO]: Command Join VC executed by ${msg.author.tag}`
      );
  }
}

module.exports = join;
