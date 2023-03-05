const Discord = require("discord.js");
const got = require("got");

class players {
  constructor() {
    this.name = "players";
    this.aliases = ["plrs"];
    this.description = "Get in-game player list";
    this.usage = ["players"];
    this.category = "Server";
  }

  async onRun(client, msg, args) {
    got("https://api.minetools.eu/query/versai.pro/19132").then((response) => {
      const list = JSON.parse(response.body);
      let embed = new Discord.MessageEmbed()
        .setImage(
          "https://github.com/versai-network/icons/blob/master/Versai_Banner.jpg?raw=true"
        )
        .setColor("#2F3136")
        .setDescription(
          `:busts_in_silhouette: **Players (${
            list.Players
          })**\n\`\`\`${list.Playerlist.toString().split(",").join(`, `)}\`\`\``
        );
      msg.channel.send(embed);
    });
    console.log(
      "\x1b[35m",
      `$[INFO]: Command players executed by ${msg.author.tag}`
    );
  }
}

module.exports = players;
