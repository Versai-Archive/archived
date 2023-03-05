const Discord = require("discord.js");
const config = require("../../storage/config.json");
const emojis = require("../../storage/emojis.json");

class unmute {
  constructor() {
    this.name = "unmute";
    this.aliases = [];
    this.description = "Unmute a member!";
    this.usage = ["unmute <member>"];
    this.category = "Moderation";
  }

  async onRun(client, msg, args) {
    const noUserEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(`${emojis.crossmark} Please provide a member to unmute.`);

    const noPermsUserEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} You don't have permission to use this command.`
      );

    const noPermsBotEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} Bot does not have permission to unmute members.`
      );

    const noMutedRoleEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} Couldn't find the role \`${config.mutedRole}\`.`
      );

    const errorEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} Something went wrong (Check Console).`
      );

    if (!msg.member.hasPermission("MANAGE_msgS"))
      return msg.channel.send(noPermsUserEmbed);
    if (!msg.guild.me.hasPermission("MANAGE_msgS"))
      return msg.channel.send(noPermsBotEmbed);

    let member = msg.guild.member(
      msg.mentions.users.first() || msg.guild.members.cache.get(args[0])
    );
    if (!member) return msg.channel.send(noUserEmbed);

    let role = msg.guild.roles.cache.find(
      (role) => role.name === config.mutedRole
    );
    if (!role) return msg.channel.send(noMutedRoleEmbed);

    const userUnmutedEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(`${emojis.checkmark} ${member} Has Been Unmuted.`);

    member.roles.remove(role.id);
    msg.channel.send(userUnmutedEmbed);
    console.log(
      "\x1b[35m",
      `$[INFO]: Command Unmute executed by ${msg.author.tag}`
    );
  }
}

module.exports = unmute;
