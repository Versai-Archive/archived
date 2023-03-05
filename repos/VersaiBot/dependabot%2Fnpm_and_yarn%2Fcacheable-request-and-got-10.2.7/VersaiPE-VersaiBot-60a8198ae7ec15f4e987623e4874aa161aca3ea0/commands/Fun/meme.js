const Discord = require("discord.js");
const got = require("got");

class meme {
  constructor() {
    this.name = "meme";
    this.aliases = [];
    this.description = "Generate a meme!";
    this.usage = ["meme"];
    this.category = "Fun";
  }

  async onRun(client, msg, args) {
    let embed = new Discord.MessageEmbed();
    got("https://www.reddit.com/r/memes/random/.json")
      .then((response) => {
        const [list] = JSON.parse(response.body);
        const [post] = list.data.children;

        const permalink = post.data.permalink;
        const memeUrl = `https://reddit.com${permalink}`;
        const memeImage = post.data.url;
        const memeTitle = post.data.title;
        const memeUpvotes = post.data.ups;
        const memeNumComments = post.data.num_comments;

        embed.setTitle(`${memeTitle}`);
        embed.setURL(`${memeUrl}`);
        embed.setColor("#2F3136");
        embed.setImage(memeImage);
        embed.setFooter(`👍 ${memeUpvotes} 💬 ${memeNumComments}`);

        msg.channel.send(embed);
      })
      .catch(console.error);
    console.log(
      "\x1b[35m",
      `$[INFO]: Command meme executed by ${msg.author.tag}`
    );
  }
}

module.exports = meme;
