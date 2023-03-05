const Discord = require("discord.js");
const config = require("../../storage/config.json");
const emojis = require("../../storage/emojis.json");
const { inspect } = require("util");

class evaluate {
  constructor() {
    this.name = "eval";
    this.aliases = ["e", "evaluate"];
    this.description = "Evaluate code through Client";
    this.usage = ["eval <code>"];
    this.category = "Admin";
  }

  async onRun(client, msg, args) {
    if (!config.devID.includes(msg.author.id)) return;

    try {
      let toEval = args[0] ? args.join(" ") : null;
      let evaluated = inspect(eval(toEval, { depth: 0 }));

      return msg.channel.send(`\`\`\`js\n${evaluated}\n\`\`\``);
    } catch (e) {
      let onError = new Discord.MessageEmbed()
        .addField(
          "Error Occurred",
          `\`\`\`js\n${e.name}\n\n${e.message}\n\`\`\``
        );
      msg.channel.send(onError);
      return;
    }
  }
}

module.exports = evaluate;
