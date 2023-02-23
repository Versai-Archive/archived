const { ApplicationCommandType, EmbedBuilder, Client, CommandInteraction, ActionRowBuilder, ButtonBuilder, ButtonStyle} = require('discord.js');
const Database = require('../../database/Database');
const MinecraftUser = require('../../database/schema/MinecraftUser');
const {discord} = require("../../storage/config");
const {interactions} = require("../../storage/interactions")
const mongoose = require("mongoose");

module.exports = {
    name: 'link',
    description: "Link your minecraft account to the discord!",
    type: ApplicationCommandType.ChatInput,
    options: [
        {
            "name": "code",
            "description": "The code that was provided in game",
            "type": 3,
            // Making this false, so that they can see what they need to do to get the code
            "required": false
        }
    ],
    cooldown: 3000,
    /**
     * @param {Client} client 
     * @param {CommandInteraction} interaction 
     */
    run: async (client, interaction) => {

        if (await Database.accountIsLinked(interaction.user)) {
            interaction.reply("Your account has already been linked to an account")
            return;
        }

        let paramCode = interaction.options.get("code")

        // If no code is provided
        if (!paramCode) {
            interaction.reply("In order to link your account, you must go in game and run `/link` in game!")
            return;
        }
        // If code
        let user = await MinecraftUser.find({ code: paramCode.value })
        user = user[0]
        if (!user) {
            if (await MinecraftUser.find({ discord_id: interaction.user.id })) {
                interaction.reply({
                    content: "Account has already been linked!",
                    ephemeral: true
                });
                return;
            }
            interaction.reply({
                content: "There is not a account to link, with this code",
                ephemeral: true
            });
            return;
        }
        // Then link the accounts
        let profile = await new MinecraftUser({
            _id: mongoose.Types.ObjectId(),
            xuid: user['xuid'],
            username: user['username'],
            code: 'LINKED',
            discord_id: interaction.user.id
        })

        await profile.save()

        await MinecraftUser.find({ code: paramCode.value }).remove().exec()

        interaction.reply(`Successfully linked ${user['username']} to <@${interaction.user.id}>`)
    }
};