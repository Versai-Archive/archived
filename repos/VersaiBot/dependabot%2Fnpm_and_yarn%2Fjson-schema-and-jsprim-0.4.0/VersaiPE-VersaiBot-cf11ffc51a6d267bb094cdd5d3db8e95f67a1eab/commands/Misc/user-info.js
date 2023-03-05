const Discord = require("discord.js");
const emojis = require("../../storage/emojis.json");
const utils = require("../../storage/utils");

let inline = true;
let resence = true;
class userinfo {
  constructor() {
    this.name = "userinfo";
    this.aliases = ["ui", "whois", "usrinfo"];
    this.description = "Get detailed information on a member";
    this.usage = ["userinfo", "userinfo[member]"];
    this.category = "Miscellaneous";
  }

  async onRun(client, msg, args) {
    const member =
      msg.mentions.members.first() ||
      msg.guild.members.cache.get(args[0]) ||
      msg.member;
    let target = msg.mentions.users.first() || msg.author;

    //#region Information
    const roles = member.roles;
    const avatar = member.user.displayAvatarURL({
      dynamic: true,
      size: 512,
      format: "png",
    });

    let onMention = `<@!${member.id}>`;
    let onID = member.id;
    let onUsername = member.user.tag;

    let onCreated = utils.formatDate(member.user.createdAt);
    let onJoined = utils.formatDate(member.joinedAt);
    let onBoosting = member.premiumSince
      ? utils.formatDate(member.premiumSince)
      : "Not Boosting";

    //#region User Presence
    const statuses = {
      online: `${emojis.online} Online`,
      idle: `${emojis.idle} Idle`,
      dnd: `${emojis.dnd} Do Not Disturb`,
      offline: `${emojis.offline} Offline`,
    };
    let onStatus = statuses[member.user.presence.status];
    let onPlatform;
    let onPlatforms = {
      desktop: "Desktop Client",
      mobile: "Mobile Client",
      web: "Web Client",
    };
    if (member.presence.status === "offline") {
      onPlatform = "None [Offline]";
    } else {
      onPlatform = member.presence.clientStatus.desktop
        ? onPlatforms.desktop
        : member.presence.clientStatus.mobile
        ? onPlatforms.mobile
        : member.presence.clientStatus.web
        ? onPlatforms.web
        : "None";
    }
    //#endregion

    let onNickname = member.nickname ? member.nickname : "None";
    let onMembers = msg.guild.members.cache
      .sort(function (a, b) {
        return a.joinedTimestamp - b.joinedTimestamp;
      })
      .map((m) => m.id);
    let onHighestRole = `<@&${roles.highest.id}>`;

    let onRoleCount = roles.cache.size == 1 ? 0 : roles.cache.size - 1;
    let onRoles =
      onRoleCount == 0
        ? "None"
        : roles.cache
            .filter((r) => r.name !== "@everyone")
            .sort(function (a, b) {
              if (a.position > b.position) return -1;
              if (a.position < b.position) return 1;
              return 0;
            })
            .map((r) => `<@&${r.id}>`)
            .join(" ");

    let perms = new Discord.Permissions(member.permissions);
    let onPermissions = utils.cleanPerms(perms);
    //#endregion

    let onInfo = new Discord.MessageEmbed()
      .setThumbnail(avatar)
      .setColor("#2F3136")
      .setAuthor(onUsername, avatar)

      .addField("Mention", onMention, true)
      .addField("Nickname", onNickname, true)
      .addField("Username", onUsername, true)

      .addField("Created At", onCreated, true)
      .addField("Joined At", onJoined, true)
      .addField("Boosting Since", onBoosting, true)

      .addField("Highest Role", onHighestRole, true)
      .addField("Status", onStatus, true)
      .addField("Platform", onPlatform, true)

      .addField(`Roles [${onRoleCount}]`, onRoles, false)
      .addField("Permissions", onPermissions, false);

    msg.channel.send(onInfo);

    /*
            
    let embed = new Discord.MessageEmbed()
      //.setAuthor(member.user.username)
      .setThumbnail(target.displayAvatarURL({ dynamic: true }))
      .setColor("#2F3136")
      .addField("Full Username", `${member.user.tag}`)
      .addField("ID", member.user.id)
      .addField(
        "Nickname",
        `${
          member.nickname !== null
            ? `${emojis.checkmark} Nickname: ${member.nickname}`
            : `${emojis.crossmark} None`
        }`
      )
      .addField(
        "Roles",
        `${
          member.roles.cache
            .filter((r) => r.id !== msg.guild.id)
            .map((roles) => `\`${roles.name}\``)
            .join(" **|** ") || "<a:crossmark:795635695808675870> No Roles"
        }`
      )
      .addField("Joined Discord On", member.user.createdAt.toDateString(), true)
      .addField("Joined Server On", member.joinedAt.toDateString(), true)
      .setFooter(`Information about ${member.user.username}`)
      .setTimestamp();

    console.log(
      `${member.joinedAt.toDateString()} at ${member.joinedAt.toTimeString()}`
    );
    msg.channel.send(embed);
    */
    console.log(
      "\x1b[35m",
      `$[INFO]: Command Userinfo executed by ${msg.author.tag}`
    );
  }
}

module.exports = userinfo;
