const Discord = require("discord.js")
const config = require('../../storage/config.json');  
const emojis = require('../../storage/emojis.json');                      

class avatar {
  constructor() {
    this.name = "avatar";
    this.aliases = ["pfp", "av"];
    this.description = "Get a users avatar!";
    this.usage = ["avatar", "avatar [user]"];
    this.category = "Miscellaneous";
  }

  async onRun(client, msg, args) {

    const member = msg.mentions.members.first() || msg.guild.members.cache.get(args[0]) || msg.member;
  
    let avatarembed = new Discord.MessageEmbed()
    .setColor("#2F3136")
    .setAuthor(`📷 ${member.user.tag}'s Avatar`)
    .setImage(member.user.displayAvatarURL({ dynamic: true, size: 512, format: "png" }))
    .setFooter(`Requested By ${msg.author.username}`, msg.author.avatarURL());
  
     msg.channel.send(avatarembed);
  
    console.log('\x1b[35m',`$[INFO]: Command avatar executed by ${msg.author.tag}`);

  }
}

module.exports = avatar;