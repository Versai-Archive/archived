const Discord = require("discord.js");
const emojis = require("../../storage/emojis.json");

class ban {
  constructor() {
    this.name = "ban";
    this.aliases = [];
    this.description = "Ban a member!";
    this.usage = ["ban <member>", "ban <member> [reason]"];
    this.category = "Moderation";
  }

  async onRun(client, msg, args) {
    const noUserEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(`${emojis.crossmark} Please provide a member to ban.`);

    const noPermsUserEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} You don't have permission to use this command.`
      );

    const noPermsBotEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} Bot does not have permission to ban members.`
      );

    const couldntFindMemberEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(`${emojis.crossmark} Couldn't find that member.`);

    const cantBanSelfEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(`${emojis.crossmark} You can't ban yourself.`);

    const roleNotHighEnoughEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} I can't ban that member due to role hierarchy.`
      );

    const errorEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} Something went wrong (Check Console).`
      );

    if (!msg.member.hasPermission("BAN_MEMBERS"))
      return msg.channel.send(noPermsUserEmbed);
    if (!msg.guild.me.hasPermission("BAN_MEMBERS"))
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
      .ban({
        reason: reason,
      })
      .catch((err) => {
        if (err) return msg.channel.send(errorEmbed);
      });

    const banEmbed = new Discord.MessageEmbed()
      .setTitle(`${emojis.checkmark} Member Was Banned`)
      .setThumbnail(msg.guild.iconURL({ dynamic: true }))
      .setColor("#2F3136")
      .addField("User Banned", member)
      .addField("Banned by", msg.author)
      .addField("Reason", reason);

    msg.channel.send(banEmbed);
    console.log(
      "\x1b[35m",
      `$[INFO]: Command ban executed by ${msg.author.tag}`
    );
  }
}

module.exports = ban;
