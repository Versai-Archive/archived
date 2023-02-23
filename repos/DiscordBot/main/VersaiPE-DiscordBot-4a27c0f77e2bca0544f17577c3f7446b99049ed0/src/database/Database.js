const { Client, TextChannel, Message, GuildMember, User } = require("discord.js");
const mongoose = require("mongoose");
const TicketManager = require("../tickets/TicketManager");
const Transcript = require("./schema/Transcript");
const logger = require("../utils/logger");
const transcriptMessage = require("./schema/TranscriptUser");
const transcriptUser = require("./schema/TranscriptUser");
const MinecraftUser = require("./schema/MinecraftUser");
const PracticeUser = require("./schema/PracticeUser");

class Database {
  /**
   *
   * @param {TextChannel} ticket
   * @param {Message[]} messages
   */
  async saveTranscript(ticket, messages) {
    let transciptProfile = await Transcript.findOne({ ChannelID: ticket.id });

    let schemaMessages = [];

    messages.forEach((m) => {
      let messageEmbeds = [];

      if (m.embeds != []) {
        messageEmbeds = m.embeds;
      }

      let message = {
        Content: m.content,
        Embeds: messageEmbeds,
        TimeSent: m.createdTimestamp,
        // TODO: replying to
        Author: {
          ID: m.author.id,
          UserProfile: m.author.avatarURL({ forceStatic: true }),
          UserName: m.author.username,
        },
      };

      schemaMessages.push(message);
    });

    if (!transciptProfile) {
      transciptProfile = await new Transcript({
        _id: mongoose.Types.ObjectId(),
        ChannelID: ticket.id,
        TicketName: ticket.name,
        Content: schemaMessages,
      });
    }

    await transciptProfile
      .save()
      .catch((err) =>
        logger.error("Error occured while saving transcript data" + err)
      );
  }

  /**
   * 
   * @param {User} user 
   */
  async accountIsLinked(user) {
    (await MinecraftUser.find({ discord_id: user.id })) ? true : false
  }

  async getLinkedData(user) {
    return await MinecraftUser.find({ discord_id: user.id })
  }

  async getAccount(xuid) {
    return await PracticeUser.find({xuid: xuid})
  } 

}

module.exports = new Database();
