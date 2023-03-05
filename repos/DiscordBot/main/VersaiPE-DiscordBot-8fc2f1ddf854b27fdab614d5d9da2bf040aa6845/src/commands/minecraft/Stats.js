const {ApplicationCommandType} = require("discord-api-types/v8");
const TicketManager = require("../../tickets/TicketManager");
const { Client, Interaction, EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle, Attachment, AttachmentBuilder } = require("discord.js");
const { connection } = require("mongoose");
const PracticeUser = require("../../database/schema/PracticeUser");
const MinecraftUser = require("../../database/schema/MinecraftUser");
const Jimp = require("jimp");
const Canvas = require("canvas");
const path = require('path');
const { accountIsLinked, getAccount, getLinkedData } = require("../../database/Database");
const logger = require("../../utils/logger");

module.exports = {
    name: "stats",
    description: "view your minecraft stats",
    type: ApplicationCommandType.ChatInput,
    cooldown: 5000,
    options: [
        {
            "name": "player",
            "description": "the stats of the player you would like to see",
            "type": 3,
            // Making this false, so that they can see what they need to do to get the code
            "required": false
        }
    ],
    /**
     * @param {Client} client 
     * @param {Interaction} interaction 
     */
    run: async (client, interaction) => {
        if (accountIsLinked(interaction.user)) {
            let data = await getAccount((await getLinkedData(interaction.user))[0]["xuid"])
            data = data[0]
            console.log(data)
            console.log(data.rank)
            let emb = new EmbedBuilder()
                .setTitle(`Stats for ${data["username"]}`)
                .setDescription(`
                    **Kills**: 5
                    **Deaths**: 0
                    **Streak**: 5
                    
                    **Rank**: ${data.rank.name}

                    **Color Scheme**: Blue, Dark Blue
                `);

            interaction.reply({ embeds: [emb] })
        } else {
            interaction.reply("Nah")
        }
    }
}