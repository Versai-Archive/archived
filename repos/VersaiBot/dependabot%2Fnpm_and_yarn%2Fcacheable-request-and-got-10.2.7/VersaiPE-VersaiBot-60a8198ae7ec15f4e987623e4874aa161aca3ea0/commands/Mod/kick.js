const Discord = require("discord.js");
const emojis = require("../../storage/emojis.json");

class kick {
  constructor() {
    this.name = "kick";
    this.aliases = [];
    this.description = "Kick a member!";
    this.usage = ["kick <member>", "kick <member> [reason]"];
    this.category = "Moderation";
  }

  async onRun(client, msg, args) {
    const noUserEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(`${emojis.crossmark} Please provide a member to kick.`);

    const noPermsUserEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} You don't have permission to use this command.`
      );

    const noPermsBotEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} Bot does not have permission to kick members.`
      );

    const couldntFindMemberEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(`${emojis.crossmark} Couldn't find that member.`);

    const cantBanSelfEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(`${emojis.crossmark} You can't kick yourself.`);

    const roleNotHighEnoughEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} I can't kick that member due to role hierarchy.`
      );

    const errorEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} Something went wrong (Check Console).`
      );

    if (!msg.member.hasPermission("KICK_MEMBERS"))
      return msg.channel.send(noPermsUserEmbed);
    if (!msg.guild.me.hasPermission("KICK_MEMBERS"))
      return msg.channel.send(noPermsBotEmbed);

    let member =
      msg.mentions.members.first() ||
      msg.guild.members.cache.get(args[0]) ||
      msg.guild.members.cache.find(
        (x) =>
          x.user.username.toLowerCase() === args.slice(0).join(" ") ||
          x.user.username === args[0]
      );

    if (!args[0]) return msg.channel.send(noUserEmbed);
    if (!member) return msg.channel.send(couldntFindMemberEmbed);
    if (!member.bannable) return msg.channel.send(roleNotHighEnoughEmbed);
    if (member.id === msg.author.id) return msg.channel.send(cantBanSelfEmbed);

    let reason = args.slice(1).join(" ");
    if (!reason) reason = "Unspecified";

    member
      .kick({
        reason: reason,
      })
      .catch((err) => {
        if (err) return msg.channel.send(errorEmbed);
      });

    const kickEmbed = new Discord.MessageEmbed()
      .setTitle(`${emojis.checkmark} Member Was kicked`)
      .setThumbnail(msg.guild.iconURL({ dynamic: true }))
      .setColor("#2F3136")
      .addField("User Kicked", member)
      .addField("Kicked by", msg.author)
      .addField("Reason", reason);

    msg.channel.send(kickEmbed);
    console.log(
      "\x1b[35m",
      `$[INFO]: Command kick executed by ${msg.author.tag}`
    );
  }
}

module.exports = kick;
