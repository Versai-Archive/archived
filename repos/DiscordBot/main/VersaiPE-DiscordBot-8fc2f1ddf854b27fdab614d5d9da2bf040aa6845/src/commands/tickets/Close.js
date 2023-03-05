const {ApplicationCommandType} = require("discord-api-types/v8");
const { Client, Interaction, EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle, CommandInteraction } = require("discord.js");
const TicketManager = require("../../tickets/TicketManager");
const {interactions} = require("../../storage/interactions");
module.exports = {
    name: "close",
    description: "Close a ticket",
    type: ApplicationCommandType.ChatInput,
    cooldown: 5000,
    /**
     * 
     * @param {Client} client 
     * @param {CommandInteraction} interaction 
     */
    run: async (client, interaction) => {
        let manager = new TicketManager();

        if (!manager.isInTicketCategory(interaction.channel)) interaction.reply({content: "> This channel isn't a ticket", ephemeral: true}); // TODO: Fix

        let ticket = interaction.channel;

        let closeButton = new ButtonBuilder()
            .setLabel("Close")
            .setCustomId(interactions.tickets.close)
            .setStyle(ButtonStyle.Danger);

        let cancelButton = new ButtonBuilder()
            .setLabel("Cancel")
            .setCustomId(interactions.tickets.cancel)
            .setStyle(ButtonStyle.Secondary)
        
        const confirmation = new ActionRowBuilder()
            .addComponents([closeButton, cancelButton])

        interaction.reply({
            components: [confirmation],
            content: "Are you sure you would like to close this ticket?"
        })
            
    }

}