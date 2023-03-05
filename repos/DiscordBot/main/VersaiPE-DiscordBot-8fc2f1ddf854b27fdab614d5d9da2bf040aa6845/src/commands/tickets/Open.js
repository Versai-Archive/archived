const {ApplicationCommandType} = require("discord-api-types/v8");
const TicketManager = require("../../tickets/TicketManager");
const { Client, Interaction, EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require("discord.js");

module.exports = {
    name: "open",
    description: "open a ticket",
    type: ApplicationCommandType.ChatInput,
    cooldown: 5000,
    /**
     * @param {Client} client 
     * @param {Interaction} interaction 
     */
    run: async (client, interaction) => {
        let manager = new TicketManager();

        if (!manager.isInTicketCategory(interaction.channel)) {
            interaction.reply({content: "This is not a ticket", ephermeral: true});
            return;
        }
    }
}