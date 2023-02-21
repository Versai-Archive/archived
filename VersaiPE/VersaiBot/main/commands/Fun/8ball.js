const Discord = require("discord.js");
const config = require("../../storage/config.json");
const emojis = require("../../storage/emojis.json");

class eightball {
  constructor() {
    this.name = "8ball";
    this.aliases = ["8b"];
    this.description = "Question the Magic 8 Ball";
    this.usage = ["8ball <question>"];
    this.category = "Fun";
  }

  async onRun(client, msg, args) {

    const noQuestion = new Discord.MessageEmbed()
    .setColor("#2F3136")
    .setDescription(`${emojis.crossmark} Please provide a question.`);

    if (!args[0]) return msg.channel.send(noQuestion);

    let onReplies = [
      //Yes [5]
      "Yes",
      "Absolutely",
      "Certainly",
      "Definitely",
      "Undoubtedly",

      //Unsure [5]
      "Unsure",
      "Try again later",
      "Uncertain",
      "Maybe",
      "Don't know",

      //No [5]
      "No",
      "Nope",
      "Absolutely not",
      "Certainly not",
      "Definitely not"
    ];

    const onReply = new Discord.MessageEmbed()
    .setColor("#2F3136")
    .addField(`Magic 8 Ball 🎱`, onReplies[Math.floor(Math.random() * onReplies.length)]);

    msg.channel.send(onReply);
  }
}

module.exports = eightball;
