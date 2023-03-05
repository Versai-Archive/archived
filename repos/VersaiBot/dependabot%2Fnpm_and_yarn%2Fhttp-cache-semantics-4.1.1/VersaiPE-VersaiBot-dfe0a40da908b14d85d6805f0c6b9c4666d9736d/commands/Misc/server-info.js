const Discord = require("discord.js");
const moment = require("moment");
const emojis = require("../../storage/emojis.json");

class serverinfo {
  constructor() {
    this.name = "serverinfo";
    this.aliases = ["si", "server-info", "srvinfo"];
    this.description = "Get detailed information about this Discord server";
    this.usage = ["serverinfo"];
    this.category = "Miscellaneous";
  }

  async onRun(client, msg, args) {
    await msg.guild.members.fetch(msg.guild.ownerID);
    const roles = msg.guild.roles.cache
      .sort((a, b) => b.position - a.position)
      .map((role) => role.toString());
    const members = msg.guild.members.cache;
    const channels = msg.guild.channels.cache;
    const emojis = msg.guild.emojis.cache;

    //Replace With The Emojis You Would Like
    const onlineEmoji = "🟢";
    const idleEmoji = "🟡";
    const dndEmoji = "🔴";
    const offlineEmoji = "⚪️";

    const filterLevels = {
      DISABLED: "Off",
      MEMBERS_WITHOUT_ROLES: "No Role",
      ALL_MEMBERS: "Everyone",
    };

    //different verification levels
    const verificationLevels = {
      NONE: "None",
      LOW: "Low",
      MEDIUM: "Medium",
      HIGH: "(╯°□°）╯︵ ┻━┻",
      VERY_HIGH: "┻━┻ ﾐヽ(ಠ益ಠ)ノ彡┻━┻",
    };

    //regional flags
    const regions = {
      brazil: ":flag_br: Brazil",
      europe: ":flag_eu: Europe",
      hongkong: ":flag_hk: Hong Kong",
      india: ":flag_in: India",
      japan: ":flag_jp: Japan",
      russia: "<:rusian:732466484533657680> Russia",
      singapore: ":flag_sg: Singapore",
      southafrica: ":flag_za: South Africa",
      sydeny: ":flag_au: Sydeny",
      "us-central": ":flag_us: US Central",
      "us-east": ":flag_us: US East",
      "us-west": ":flag_us: US West",
      "us-south": ":flag_us: US South",
    };

    //start of the embed
    const embed = new Discord.MessageEmbed()
      .setAuthor(`Server Information for ${msg.guild.name}`)
      .setColor("2F3136")
      .setThumbnail(msg.guild.iconURL({ dynamic: true }))
      .addField("General", [
        `**❯ Name:** ${msg.guild.name}`,
        `**❯ ID:** ${msg.guild.id}`,
        `**❯ Owner:** ${msg.guild.owner.user.tag}`,
        `**❯ Region:** ${regions[msg.guild.region]}`,
        `**❯ Boost Tier:** ${
          msg.guild.premiumTier ? `Tier ${msg.guild.premiumTier}` : "None"
        }`,
        `**❯ Explicit Filter:** ${
          filterLevels[msg.guild.explicitContentFilter]
        }`,
        `**❯ Verification Level:** ${
          verificationLevels[msg.guild.verificationLevel]
        }`,
        `**❯ Time Created:** ${moment(msg.guild.createdTimestamp).format(
          "LT"
        )} ${moment(msg.guild.createdTimestamp).format("LL")} ${moment(
          msg.guild.createdTimestamp
        ).fromNow()}`,
        `**❯** [**Server Icon**](${msg.guild.iconURL({ dynamic: true })})`,
        `**❯ Features:**`,
        `${msg.guild.features.join(", ") || "None"}`,
        "\u200b",
      ])
      .addField("Statistics", [
        `**❯ Role Count:** ${roles.length}`,
        `**❯ Emoji Count:** ${emojis.size}`,
        `**❯ Regular Emoji Count:** ${
          emojis.filter((emoji) => !emoji.animated).size
        }`,
        `**❯ Animated Emoji Count:** ${
          emojis.filter((emoji) => emoji.animated).size
        }`,
        `**❯ Text Channels:** ${
          channels.filter((channel) => channel.type === "text").size
        }`,
        `**❯ Voice Channels:** ${
          channels.filter((channel) => channel.type === "voice").size
        }`,
        `**❯ Boost Count:** ${msg.guild.premiumSubscriptionCount || "0"}`,
        "\u200b",
      ])
      .addField("Presence", [
        `**❯ Total Member:** ${msg.guild.memberCount}`,
        `**❯ Humans:** ${members.filter((member) => !member.user.bot).size}`,
        `**❯ Bots:** ${members.filter((member) => member.user.bot).size}`,
        `**❯ ${onlineEmoji} Online:** ${
          members.filter((member) => member.presence.status === "online").size
        }`,
        `**❯ ${idleEmoji} Idle:** ${
          members.filter((member) => member.presence.status === "idle").size
        }`,
        `**❯ ${dndEmoji} Do Not Disturb:** ${
          members.filter((member) => member.presence.status === "dnd").size
        }`,
        `**❯ ${offlineEmoji} Offline:** ${
          members.filter((member) => member.presence.status === "offline").size
        }`,
        "\u200b",
      ])
      .setTimestamp()
      .setImage(
        "https://github.com/versai-network/icons/blob/master/Versai_Banner.jpg?raw=true"
      )
      .setFooter(`© Versai`, `${client.user.displayAvatarURL()}`);

    //sending the embed
    msg.channel.send(embed).catch();
    console.log(
      "\x1b[35m",
      `$[INFO]: Command serverinfo executed by ${msg.author.tag}`
    );
  }
}

module.exports = serverinfo;
