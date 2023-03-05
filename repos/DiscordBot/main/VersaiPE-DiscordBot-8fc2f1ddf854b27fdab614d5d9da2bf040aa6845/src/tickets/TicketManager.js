const {GuildChannel, Message, TextChannel, User, ModalBuilder, TextInputBuilder, TextInputStyle, ActionRowBuilder} = require("discord.js");
const config = require("../storage/config");
const interactions = require("../storage/interactions");
const logger = require("../utils/logger");

class TicketManager {

    /**
     *
     * @param channel
     *
     * @return Array
     */
    async getAllMessages(channel) {

        let messages = [];

        // Create message pointer
        let message = await channel.messages
            .fetch({ limit: 1 })
            .then(messagePage => (messagePage.size === 1 ? messagePage.at(0) : null));

        while (message) {
            await channel.messages
                .fetch({ limit: 100, before: message.id })
                .then(messagePage => {
                    messagePage.forEach(msg => messages.push(msg));

                    // Update our message pointer to be last message in page of messages
                    message = 0 < messagePage.size ? messagePage.at(messagePage.size - 1) : null;
                })
        }
        return messages
    }

    /**
     *
     * @param {TextChannel} channel
     * @return {boolean}
     */
    isInTicketCategory(channel) {
        return (channel.parent.id === config.tickets.ticket_category.toString())
    }

    /**
     * 
     * @returns {ModalBuilder}
     */
    getInGameNameForm() {
        return new ModalBuilder()
            .setCustomId(interactions.interactions.applications.modal.ign)
            .setTitle("Lets start your application!")
            .setComponents(
                new ActionRowBuilder().addComponents(
                    new TextInputBuilder()
                        .setCustomId(interactions.interactions.applications.modal.ign_question)
                        .setLabel("What is your Minecraft Username?")
                        .setRequired(true)
                        .setMaxLength(256)
                        .setStyle(TextInputStyle.Short)
                )
            )
    }

}

module.exports = TicketManager;