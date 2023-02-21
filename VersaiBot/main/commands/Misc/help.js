const Discord = require("discord.js");
const fs = require("fs");
const config = require("../../storage/config.json");
const emojis = require("../../storage/emojis.json");

class help {
  constructor() {
    this.name = "help";
    this.aliases = ["h", "bothelp"];
    this.description = "A helpful command!";
    this.usage = ["help", "help [command]"];
    this.category = "Miscellaneous";
  }

  async onRun(client, msg, args) {
    if (!args[0]) {
      const embed = new Discord.MessageEmbed()
        .setColor("#2F3136")
        .setDescription(
          `Use **${config.prefix}help <command>** for detailed information!`
        )
        .setFooter(
          `Required: <> || Optional: []`,
          msg.author.avatarURL({ dynamic: true, format: "png" })
        );

      fs.readdirSync(`./commands/`)
        .filter((d) => d !== "Admin")
        .forEach((dir) => {
          let files = fs
            .readdirSync(`./commands/${dir}/`)
            .filter((f) => f.endsWith(".js"))
            .map((f) => f.replace(".js", ""))
            .join(", ");
          embed.addField(dir, files);
        });

      msg.channel.send(embed);
    } else {
      let cmd = client.commands.get(
        args[0].toLowerCase() ||
          client.commands.get(client.aliases.get(args[0].toLowerCase()))
      );
      if (!cmd) {
        let onDenied = new Discord.MessageEmbed()
          .setColor("#2F3136")
          .setDescription(`${emojis.crossmark} Please provide a valid command`);
        return msg.channel.send(onDenied);
      }
      let dembed = new Discord.MessageEmbed()
        .setColor("#2F3136")
        .setTitle(
          `[${cmd.category}] - ${cmd.name[0].toUpperCase() + cmd.name.slice(1)}`
        );
      dembed.addField("Information", cmd.description, true);
      cmd.aliases.length <= 0 || cmd.aliases[0].length <= 0
        ? dembed.addField("Aliases", "None", true)
        : dembed.addField("Aliases", cmd.aliases.join(", "), true);
      dembed.addField("Usage", cmd.usage.join("\n"));
      msg.channel.send(dembed);
    }
    console.log(
      "\x1b[35m",
      `$[INFO]: Command help executed by ${msg.author.tag}`
    );
  }
}

module.exports = help;
