const TikTokScraper = require("tiktok-scraper");
var dateFormat = require("dateformat");
class tiktok {
  constructor() {
    this.name = "tiktok";
    this.aliases = [];
    this.description = "Tiktok on the clock";
    this.usage = "tiktok";
    this.category = "Admin";
  }

  async onRun(client, last) {
    try {
      const posts = await TikTokScraper.user("versai.network", {
        number: 1,
        sessionList: ["sid_tt=58ba9e34431774703d3c34e60d584475;"],
      });
      if (posts.collector[0].webVideoUrl !== last) {
        const feed = new client.Discord.MessageEmbed().setColor("#b434eb");
        feed.setTitle(`Versai Network`);
        feed
          .setURL(posts.collector[0].webVideoUrl)
          .setThumbnail(posts.collector[0].authorMeta.avatar)
          .setImage(posts.collector[0].covers.origin)
          .setDescription(`New post from Versai on TikTok!`);
        if (posts.collector[0].text) {
          feed.addField("Post Description", `${posts.collector[0].text}`);
        }
        var date = new Date(posts.collector[0].createTime * 1000);
        feed
          .addField("Post Date", dateFormat(date, "dddd, mmmm dS, yyyy"))
          .addField(
            "Video Link",
            `[Click here](${posts.collector[0].webVideoUrl})`
          );
        client.channels.cache.get("708293818318979084").send({ embed: feed });
        writeLastPost(last);
      }
    } catch (error) {
      const embederr = new client.Discord.MessageEmbed()
        .setColor("#FF0000")
        .setTitle(`Error`)
        .setDescription(`\`\`\`\n${error.toString()}\`\`\``);
      client.channels.cache.get("774115220498939904").send({ embed: embederr });
    }
  }

  lastPost(last) {
    const fs = require("fs");
    var name = "../storage/config.json";
    var m = JSON.parse(fs.readFileSync(name).toString());
    m.lastPost = last;
    fs.writeFileSync(name, JSON.stringify(m));
  }
}

module.exports = tiktok;
