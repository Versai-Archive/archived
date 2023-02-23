const {
  ActionRowBuilder,
  StringSelectMenuBuilder,
  EmbedBuilder,
} = require("@discordjs/builders");
const {
  ActionRow,
  CategoryChannel,
  PermissionsBitField,
  TextChannel,
  GuildMember,
  Guild,
  Colors,
  MessageCollector,
  ButtonBuilder,
} = require("discord.js");
const { colors } = require("../storage/config");
const config = require("../storage/config");
const { interactions } = require("../storage/interactions");
const { ButtonStyle } = require("discord-api-types/v8");

class TicketHandler {
  /**
   *
   * @param {TextChannel} channel
   */
  async sendDefaultMessage(channel) {
    const rows = new ActionRowBuilder().addComponents(
      new StringSelectMenuBuilder()
        .setCustomId(interactions.tickets.create.application.general)
        .addOptions(
          {
            label: "PVP",
            description: "Apply to moderate the practice server",
            value: "pvp",
            emoji: {
              name: "⚔️",
            }
          },
          {
            label: "Builder",
            description: "Apply to build maps for the server!",
            value: "builder",
            emoji: {
              name: "🏗️",
            },
          },
          {
            label: "Developer",
            description: "Apply to develop things for the server",
            value: "developer",
            emoji: {
              name: "🧑‍💻",
            },
          },
          {
            label: "Media",
            description: "Apply for the media rank, to promote Versai",
            value: "media",
            emoji: {
              name: "📱",
            },
          }
        )
    );

    let embed = new EmbedBuilder().setAuthor({ name: "Versai-Applications" })
      .setDescription(`
                When creating a ticket you agree to all of the following\n\n
                - You have a working microphone and can participate in a voicechat interview\n
                - You are above the age of 13\n
                - You are active on the server`);

    channel.send({ components: [rows], embeds: [embed] });
  }

  /**
   *
   * @param {string} type
   * @param {Guild} guild
   * @param {GuildMember} creator
   */
  async createTicket(type, guild, creator) {
    let cat = guild.channels.cache.find(
      (c) =>
        c instanceof CategoryChannel && c.id == config.tickets.ticket_category
    );

    let username = creator.user.username;

    if (!username) {
      username = Math.round(9999 * Math.random())
        .toString()
        .replace(".", "")
        .substring(0, 5);
    }

    username = username.toString().replace(/[^a-zA-Z0-9 ]/g, "");

    switch (type) {
      case "pvp":
        let pvpApp = await guild.channels.create({
          name: `🔓・pvp-${username}`,
          parent: cat,
          permissions: [
            {
              id: config.roles.recruitment,
              allow: [
                PermissionsBitField.Flags.ViewChannel,
                PermissionsBitField.Flags.SendMessages,
              ],
            },
            {
              id: creator.user.id,
              allow: [
                PermissionsBitField.Flags.ViewChannel,
                PermissionsBitField.Flags.SendMessages,
              ],
            },
          ],
        });

        // Send the message with the data, and actions that can be made with the tickets
        // Using a function to help make it look a little nicer

        await this.sendDefaultTicketMessage(pvpApp);

        await pvpApp.send(
          `<@${creator.user.id}> Your application will begin in 10 second's!`
        );

        setTimeout(() => {
          this.startApp(pvpApp, "PVP", creator);
        }, 10000);
        break;

      case "builder":
        let buildApp = await guild.channels.create({
          name: `🔓・builder-${username}`,
          parent: cat,
          permissions: [
            {
              id: config.roles.recruitment, // TODO
              allow: [
                PermissionsBitField.Flags.ViewChannel,
                PermissionsBitField.Flags.SendMessages,
              ],
            },
            {
              id: creator.user.id,
              allow: [
                PermissionsBitField.Flags.ViewChannel,
                PermissionsBitField.Flags.SendMessages,
              ],
            },
          ],
        });

        // buildApp.permissionOverwrites({

        // })

        await this.sendDefaultTicketMessage(buildApp);

        await buildApp.send(
          `<@${creator.user.id}> Your application will begin in 10 second's!`
        );

        setTimeout(() => {
          this.startApp(buildApp, "BUILDER", creator);
        }, 10000);
        break;

      case "developer":
        let devApp = await guild.channels.create({
          name: `🔓・dev-${username}`,
          parent: cat,
          permissions: [
            {
              id: config.roles.recruitment, // TODO
              allow: [
                PermissionsBitField.Flags.ViewChannel,
                PermissionsBitField.Flags.SendMessages,
              ],
            },
            {
              id: creator.user.id,
              allow: [
                PermissionsBitField.Flags.ViewChannel,
                PermissionsBitField.Flags.SendMessages,
              ],
            },
          ],
        });
        await this.sendDefaultTicketMessage(devApp);

        await devApp.send(
          `<@${creator.user.id}> Your application will begin in 10 second's!`
        );

        setTimeout(() => {
          this.startApp(devApp, "DEVELOPER", creator);
        }, 10000);
        break;

      case "media":
        let mediaApp = await guild.channels.create({
          name: `🔓・media-${username}`,
          parent: cat,
          permissions: [
            {
              id: config.roles.recruitment, // TODO
              allow: [
                PermissionsBitField.Flags.ViewChannel,
                PermissionsBitField.Flags.SendMessages,
              ],
            },
            {
              id: creator.user.id,
              allow: [
                PermissionsBitField.Flags.ViewChannel,
                PermissionsBitField.Flags.SendMessages,
              ],
            },
          ],
        });

        await this.sendDefaultTicketMessage(mediaApp);

        await mediaApp.send(
          `<@${creator.user.id}> Your application will begin in 10 second's!`
        );

        setTimeout(() => {
          this.startApp(mediaApp, "MEDIA", creator);
        }, 10000);
        break;
    }
  }

  /**
   *
   * @param {TextChannel} channel
   * @param {string} type
   */
  async sendDefaultTicketMessage(channel) {
    let embed = new EmbedBuilder()
      .setColor(Colors.DarkPurple)
      .setTitle("Versai Tickets")
      .setDescription(
        "You have created a ticket, please be patient and support will be with you shortly."
      );

    let debugButtons = new ActionRowBuilder().addComponents(
      new ButtonBuilder()
        .setCustomId(interactions.tickets.save)
        .setStyle(ButtonStyle.Danger)
        .setEmoji({
          name: "📃",
        })
        .setLabel("Save Transcript")
    );

    channel.send({ embeds: [embed], components: [debugButtons] });
  }

  /**
   *
   * @param {TextChannel} ticket
   * @param {string} type
   * @param {GuildMember} creator
   */
  async startApp(ticket, type, creator) {
    let counter = 0;

    let questions;

    switch (type) {
      case "PVP":
        questions = config.questions.pvp;
        break;
      case "BUILDER":
        questions = config.questions.builder;
        break;
      case "DEVELOPER":
        questions = config.questions.developer;
        break;
      case "MEDIA":
        questions = config.questions.media;
        break;
    }

    const filter = (m) => m.author.id === user.id;

    let user = creator.user;

    const collector = new MessageCollector(ticket, {
      filter: filter,
      max: questions.length,
      time: 1000 * 60 * 30, // 30 minutes
    });

    let embed = new EmbedBuilder()
      .setAuthor({ name: "Versai Applications" })
      .setColor(Colors.Green)
      .setTitle(user.tag + `'s ${type.toLowerCase()} Application`)
      .setDescription(questions[counter++]);

    let questionsMessage = await ticket.send({ embeds: [embed] });
    collector.on("collect", (m, c) => {
      m.delete();
      if (counter < questions.length) {
        embed.setDescription(questions[counter++]);
        questionsMessage.edit({ embeds: [embed] });
      }
    });

    collector.on("end", async (coll) => {
      // not all questions answered
      if (coll.size < questions.length) {
        // Channel was deleted before the questions were answered
        if (!ticket) {
          return;
        }
        try {
          ticket.send({
            embeds: [
              new EmbedBuilder()
                .setColor(Colors.Red)
                .setTitle("Application Timed Out!")
                .setDescription(
                  "The user took too long to answer the questions, so their application timed out!"
                ),
            ],
          });
        } catch (e) {
          console.error(e)
        }
      }

      let responseEmbed = new EmbedBuilder()
        .setTitle(user.tag + `'s ${type.toLowerCase()} Application`)
        .setColor(Colors.Green);

      let i = 0;
      coll.forEach(async (m) => {
        responseEmbed.setTitle(questions[i]);
        responseEmbed.setDescription(m.content);
        i++;
        await ticket.send({ embeds: [responseEmbed] });
      });
    });
  }
}

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

module.exports = new TicketHandler();
