const { ApplicationCommandType, EmbedBuilder} = require('discord.js');
const {discord} = require("../../storage/config");

module.exports = {
    name: 'ping',
    description: "get the ping of the bot",
    type: ApplicationCommandType.ChatInput,
    cooldown: 3000,
    run: async (client, interaction) => {

        const pingEmbed = new EmbedBuilder();
        pingEmbed.setDescription(`\`\`\`Websocket: ${Math.round(client.ws.ping)}ms\`\`\``)

        interaction.reply({ embeds: [pingEmbed] });

    }
};