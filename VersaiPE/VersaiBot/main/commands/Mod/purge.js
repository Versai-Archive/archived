const Discord = require("discord.js");
const emojis = require("../../storage/emojis.json");

class purge {
  constructor() {
    this.name = "purge";
    this.aliases = ["clear"];
    this.description = "Clear a channel (100 Messages Max)";
    this.usage = ["purge <#>"];
    this.category = "Moderation";
  }

  async onRun(client, msg, args) {
    const toManymsgdEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} Error, you can only delete between 2 and 100 msgs at one time.`
      );

    const invalidAmoutembed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} Please mention the amount of msg that you want to delete.`
      );

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

    if (!msg.member.hasPermission("MANAGE_msgS"))
      return msg.channel.send(noPermsUserEmbed);
    if (!msg.guild.me.hasPermission("MANAGE_msgS"))
      return msg.channel.send(noPermsBotEmbed);

    let msgcount = parseInt(args[0]);
    if (isNaN(msgcount)) return msg.channel.send(invalidAmoutembed);
    if (msgcount > 100) return msg.channel.send(toManymsgdEmbed);
    if (msgcount < 2) return msg.channel.send(toManymsgdEmbed);

    const successEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.checkmark} Succesfully purged \`${msgcount}\` msgs!`
      );

    msg.channel.msgs
      .fetch({ limit: msgcount })
      .then((msgs) => msg.channel.bulkDelete(msgs, true));
    msg.channel.send(successEmbed);
    console.log(
      "\x1b[35m",
      `$[INFO]: Command purge executed by ${msg.author.tag}`
    );
  }
}

module.exports = purge;
