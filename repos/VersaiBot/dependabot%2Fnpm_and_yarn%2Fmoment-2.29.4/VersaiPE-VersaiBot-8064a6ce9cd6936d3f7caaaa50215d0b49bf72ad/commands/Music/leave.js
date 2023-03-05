const Discord = require("discord.js");
const config = require("../../storage/config.json");
const emojis = require("../../storage/emojis.json");

class leave {
  constructor() {
    this.name = "leave";
    this.aliases = ["disconnect"];
    this.description = "Disconnects the bot from a voice channel";
    this.usage = ["leave"];
    this.category = "Music";
  }

  async onRun(client, msg, args) {

    const noPermsUserEmbed = new Discord.MessageEmbed()
    .setColor("#2F3136")
    .setDescription(`${emojis.crossmark} You don't have permission to use this command.`);
    const notInVCUser = new Discord.MessageEmbed()
    .setColor("#2F3136")
    .setDescription(`${emojis.crossmark} You are not in a voice channel.`);
    const notInVCBot = new Discord.MessageEmbed()
    .setColor("#2F3136")
    .setDescription(`${emojis.crossmark} I am not in a voice channel.`);
    const notInBotVC = new Discord.MessageEmbed()
    .setColor("#2F3136")
    .setDescription(`${emojis.crossmark} You are not in the same voice channel.`);

    if (!msg.member.permissions.has("MOVE_MEMBERS")) return msg.channel.send(noPermsUserEmbed);
    if (!msg.member.voice.channelID) return msg.channel.send(notInVCUser);
    if (!msg.guild.me.voice.channelID) return msg.channel.send(notInVCBot);
    
    let vc = msg.guild.channels.cache.get(msg.guild.me.voice.channelID);
    if (vc.id !== msg.guild.channels.cache.get(msg.member.voice.channelID).id) return msg.channel.send(notInBotVC);

    vc.leave();
    console.log(
        "\x1b[35m",
        `$[INFO]: Command Leave VC executed by ${msg.author.tag}`
      );
  }
}

module.exports = leave;
