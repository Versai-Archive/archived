const Discord = require("discord.js");
const ms = require("ms");
const config = require("../../storage/config.json");
const emojis = require("../../storage/emojis.json");

class tempmute {
  constructor() {
    this.name = "tempmute";
    this.aliases = ["mute"];
    this.description = "Mute a member!";
    this.usage = [
      "tempmute <member> <time>",
      "tempmute <member> <reason> [reason]",
    ];
    this.category = "Moderation";
  }

  async onRun(client, msg, args) {
    const noUserEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(`${emojis.crossmark} Please provide a member to mute.`);

    const noPermsUserEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} You don't have permission to use this command.`
      );

    const noPermsBotEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} Bot does not have permission to mute members.`
      );

    const noMutedRoleEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} Couldn't find the role \`${config.mutedRole}\`.`
      );

    const nospecifTimeEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} Please provide a valid mute length.`
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

    let time = args[1];
    if (!time) return msg.channel.send(nospecifTimeEmbed);

    let reason = args.slice(2).join(" ");
    if (!reason) reason = "Unspecified";

    member.roles.add(role.id);

    const muteEmbed = new Discord.MessageEmbed()
      .setTitle(`${emojis.checkmark} Member Was Muted`)
      .setThumbnail(msg.guild.iconURL({ dynamic: true }))
      .setColor("#2F3136")
      .addField("User Muted", member)
      .addField("Muted by", msg.author)
      .addField("Reason", reason)
      .addField("Duration", time);

    msg.channel.send(muteEmbed);

    setTimeout(function () {
      member.roles.remove(role.id);
    }, ms(time));
    console.log(
      "\x1b[35m",
      `$[INFO]: Command Temp-Mute executed by ${msg.author.tag}`
    );
  }
}

module.exports = tempmute;
