const { ApplicationCommandType, EmbedBuilder, PermissionsBitField, Client, CommandInteraction, Colors, ActionRowBuilder, ButtonBuilder, ButtonStyle, PermissionFlagsBits, AttachmentBuilder} = require('discord.js');
const { fstat } = require('fs-extra');
const { roles } = require('../../storage/config');
const interactions = require('../../storage/interactions');
const TicketHandler = require('../../tickets/ticketCreationHandler');
const fs = require("fs");
const path = require('path');

module.exports = {
    name: 'initalize',
    description: "Send the message to tickets",
    type: ApplicationCommandType.ChatInput,
    cooldown: 3000,
    options: [
        {
            "name": "initalize",
            "description": "what you are initalizing",
            "type": 3,
            "required": true,
            "choices": [
                {
                    "name": "tickets",
                    "value": "ticket_init"
                },
                {
                    "name": "verification",
                    "value": "verification_init"
                },
                {
                    "name": "roles",
                    "value": "roles_init"
                }
            ]
        }
    ],
    default_member_permssions: [
        PermissionsBitField.Flags.Administrator
    ],
    /**
     * 
     * @param {Client} client 
     * @param {CommandInteraction} interaction 
     * @returns 
     */
    run: async (client, interaction) => {
        if (!interaction.guild) {
            interaction.reply("No guild found")
            return
        }
        if (interaction.member.permissions.has(PermissionFlagsBits.Administrator) || interaction.member.id === "383010755168960512") {
            switch (interaction.options.get("initalize").value) {
                case "ticket_init":
                    await TicketHandler.sendDefaultMessage(interaction.channel) || interaction.reply({ ephemeral: true, content: "Sent ticket panel" })
                break;
                
                case "verification_init":
                    let embed = new EmbedBuilder()
                        .setColor(Colors.Blurple)
                        .setTitle("Please click the Verify button to continue into the server!")

                    let button = new ActionRowBuilder()
                        .addComponents(
                            new ButtonBuilder()
                                .setCustomId(interactions.interactions.verify)
                                .setLabel("Verify")
                                .setStyle(ButtonStyle.Primary)
                                .setEmoji({
                                    name: "⭐"
                                })
                        )

                    interaction.channel.send( {embeds: [embed], components: [button]} )
                break;

                case "roles_init":
                    let roles = ""

                    interaction.guild.roles.cache.sort((a, b) => b.position - a.position).forEach((r, i) => {
                        roles += `-- ${r.name} -- \n\n`
                        r.members.forEach(m => {
                            roles += `${m.user.tag} \n`
                        })
                        roles += "\n\n"
                    })

                    let file = new AttachmentBuilder()
                        .setFile(Buffer.from(roles, 'utf-8'))
                        .setName("versai-roles.txt")
                        .setDescription("All of the members in the Versai server with there roles")

                    interaction.user.send({ files: [file] })
                break;
            }
            return;
        }
        interaction.reply("You do not have permission to run this command")
    }
};