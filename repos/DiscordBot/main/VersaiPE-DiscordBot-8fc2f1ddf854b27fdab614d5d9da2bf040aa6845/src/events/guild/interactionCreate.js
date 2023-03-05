const { EmbedBuilder, Collection, PermissionsBitField, ActionRowBuilder, ButtonBuilder, Colors, ButtonStyle, Client, CategoryChannel, GuildChannel, TextChannel } = require('discord.js');
const ms = require('ms');
const Database = require('../../database/Database');
const config = require('../../storage/config');
const { roles, channels } = require('../../storage/config');
const {interactions} = require("../../storage/interactions");
const TicketHandler = require('../../tickets/ticketCreationHandler');
const TicketManager = require("../../tickets/TicketManager");
const logger = require('../../utils/logger');

/**
 * 
 * @param {Client} client 
 * @returns 
 */
module.exports = async (client, interaction) => {
    const command = client.commands.get(interaction.commandName);

    if (interaction.type === 3) { // Button

        let applicationLogChannel = await client.channels.cache.get(config.channels.logs.applications);

        switch (interaction.customId) {
            case interactions.applications.accept:
                // Accepted the terms and agreements for the application
                // Ask what they would like to apply for

                // let embed = new EmbedBuilder()
                //     .setColor("Red")
                //     .setTitle("What would you like to apply for?")
                //     .setAuthor({ name: 'Versai-Applications', iconURL: client.avatarURL })
                //     .setDescription("Please select which team you would like to apply for");

                // let buttons = new ActionRowBuilder()
                //     .setComponents(
                //         new ButtonBuilder()
                //             .setCustomId(interactions.applications.buttons.pvp)
                //             .setLabel("PVP")
                //             .setStyle("Primary"),
                //         new ButtonBuilder()
                //             .setCustomId(interactions.applications.buttons.builder)
                //             .setLabel("Builder")
                //             .setStyle("Primary"),
                //         new ButtonBuilder()
                //             .setCustomId(interactions.applications.buttons.developer)
                //             .setLabel("Developer")
                //             .setStyle("Primary"),
                //         new ButtonBuilder()
                //             .setCustomId(interactions.applications.buttons.media)
                //             .setLabel("Media")
                //             .setStyle("Primary"),
                //     );

                // Start sending the applications questions

                // Prevent it from saying that the interaction failed even though it did not
                interaction.deferUpdate();
                // interaction.message.edit({embeds:[embed], components: [buttons]})
            break;

            case interactions.applications.deny:
                // Deny the applications terms and agreements
                let declineEmbed = new EmbedBuilder()
                        .setColor("Red")
                        .setTitle("Terms not agreed with")
                        .setDescription("Because you failed to meet the requirments, this application will now be closed");

                interaction.deferUpdate();
                interaction.message.edit({embeds: [declineEmbed]});

                let ticket = interaction.message.channel;

                setTimeout(() => {
                    ticket.delete('User declined terms and agreements');
                }, 5000)
                        
            break;

            case interactions.tickets.create.application.general:

                let cat = interaction.guild.channels.cache.filter(
                  (c) =>
                        c.parentId == config.tickets.ticket_category
                );
          
                let username = interaction.user.username;
                    
                if (!username) {
                  username = Math.round(9999 * Math.random())
                    .toString()
                    .replace(".", "")
                    .substring(0, 5);
                }
              
                username = username.toString().replace(/[^a-zA-Z0-9 ]/g, "");

                let found = cat.find((cha) => {return cha.name.includes(username.toLowerCase().replaceAll(" ", "-"))})

                if (found) {
                    interaction.reply({
                        content: "You already have an open application! <#" + found.id + ">",
                        ephemeral: true
                    });
                    return
                }

                switch (interaction.values[0]) {

                    case "pvp":
                        await TicketHandler.createTicket("pvp", interaction.member.guild, interaction.member)
                        interaction.reply({content: "\`\`\`Created a PVP Application!\`\`\`", ephemeral: true});
                        // Do this after the modal is submitted
                        // await TicketHandler.createTicket("pvp", interaction.member.guild, interaction.member)
                        // Cant reply 2 times
                        // interaction.reply({content: "\`\`\`Created a PVP Application!\`\`\`", ephemeral: true});
                    break;

                    case "builder":
                        await TicketHandler.createTicket("builder", interaction.member.guild, interaction.member)
                        interaction.reply({content: "\`\`\`Created a Builder Application!\`\`\`", ephemeral: true});
                    break;

                    case "developer":
                        await TicketHandler.createTicket("developer", interaction.member.guild, interaction.member)
                        interaction.reply({content: "\`\`\`Created a Developer Application!\`\`\`", ephemeral: true});
                    break;

                    case "media":
                        await TicketHandler.createTicket("media", interaction.member.guild, interaction.member)
                        interaction.reply({content: "\`\`\`Created a Media Application!\`\`\`", ephemeral: true});
                    break;

                }

            break;

            // TICKET SAVE TRANSCRIPT

            case interactions.tickets.save:
                // Get messages
                let manager = new TicketManager();
                let messages = await manager.getAllMessages(interaction.channel)

                let formatted = [];

                await Database.saveTranscript(interaction.channel, messages)

                let transcriptSaveEmbed = new EmbedBuilder()
                    .setColor(Colors.Green)
                    .setDescription(`Saved transcript to <#${config.channels.logs.applications}>`)

                // Send a message to the user
                interaction.channel.send({
                    embeds: [transcriptSaveEmbed]
                })
                // Send a message to the logs

                if (!applicationLogChannel) return;

                let applicationSaveEmbed = new EmbedBuilder()
                    .setColor(Colors.Yellow)
                    .setTitle("Ticket Saved!")
                    .setAuthor({ 
                        name: interaction.user.tag, 
                        iconURL: interaction.member.user.avatarURL() 
                    })
                    .setFields([
                        {
                            name: "Application Name",
                            value: interaction.channel.name
                        },
                        {
                            name: "Transcript",
                            value: `[**Transcript**](http://versai.pro:5173/transcripts/${interaction.channel.id})`
                        }
                    ])
                    .setTimestamp(Date.now())

                applicationLogChannel.send({ embeds: [applicationSaveEmbed] });
                interaction.deferUpdate()
            break;

            // TICKET CLOSE

            case interactions.tickets.close:

                let t = interaction.channel;

                let closeEmbed = new EmbedBuilder()
                    .setColor(Colors.Yellow)
                    .setDescription("Ticket Closed by <@" + interaction.user.id + ">")

                interaction.reply({content: "Ticket Closed", ephemeral: true})
                // delete the interaction message

                if (interaction.ephemeral) {
                    await interaction.message.delete()
                }

                // set to the locked icon
                t.setName(t.name.replace("🔓", "🔒"))

                let ticketControlsEmbed = new EmbedBuilder()
                    .setDescription("\`\`\`Support team ticket controls\`\`\`")

                let ticketControlButtons = new ActionRowBuilder()
                    .addComponents(
                            new ButtonBuilder()
                            .setLabel("Transcript")
                            .setEmoji({
                                name: "📃"
                            })
                            .setStyle(ButtonStyle.Secondary)
                            .setCustomId(interactions.tickets.save),
                        new ButtonBuilder()
                            .setLabel("Open")
                            .setEmoji({
                                name: "🔓"
                            })
                            .setStyle(ButtonStyle.Secondary)
                            .setCustomId(interactions.tickets.open),
                        new ButtonBuilder()
                            .setLabel("Delete")
                            .setEmoji({
                                name: "⛔"
                            })
                            .setStyle(ButtonStyle.Secondary)
                            .setCustomId(interactions.tickets.delete)
                    )

                t.send({embeds: [closeEmbed]})
                t.send({embeds: [ticketControlsEmbed], components: [ticketControlButtons]})

            break;

            case interactions.tickets.delete:
                let tick = interaction.channel
                let deleteEmbed = new EmbedBuilder()
                    .setTitle("Deleting Ticket")
                    .setDescription("Ticket will be deleted in 5 seconds")
                    .setColor(Colors.Red)
                let deleteTicketLog = new EmbedBuilder()
                    .setTitle("Ticket Deleted")
                    .setFields({
                        name: "Deleted By",
                        value: `<@${interaction.member.id}>`,
                        inline: true
                    }, {
                        name: "Ticket",
                        value: tick.name,
                        inline: true
                    })
                    .setColor(Colors.Red)
                    .setAuthor({
                        name: interaction.user.username,
                        iconURL: interaction.user.avatarURL()
                    })
                if (applicationLogChannel) {
                    applicationLogChannel.send({ embeds: [deleteTicketLog] })   
                }
                interaction.reply({ embeds: [deleteEmbed] })
                setTimeout(async () => {
                    try {
                        await tick.delete()
                    } catch (e) {
                        console.error(e.rawError)
                    }
                }, 1000*5)
            break;

            case interactions.verify:
                let role = await interaction.guild.roles.cache.find(role => role.id === roles.verified);
                if (!role) {
                    interaction.reply({ content: "There was an error finding the verified role!", ephemeral: true })
                    return;
                }

                if (interaction.member.roles.cache.has(roles.verified)) {
                    interaction.reply({ content: "You are already verified!", ephemeral: true })
                    return;
                }

                // Register user to the db
                // await Database.saveUserData(interaction.user)

                interaction.member.roles.add(role)
                interaction.reply({ content: "You have now been verified!", ephemeral: true })
                let channel = await interaction.client.channels.fetch(channels.main);
                if (!channel) {
                    return;
                }

                let welcomeEmbed = new EmbedBuilder()
                    .setColor(Colors.DarkPurple)
                    .setTitle(`Welcome to ${interaction.guild.name} !`)
                    .setDescription(`Hello <@${interaction.user.id}> ! Welcome to the Versai discord server!`)
                    .setTimestamp(Date.now())

                channel.send({ embeds: [welcomeEmbed] })
            break;

            case interactions.linking.confirm:
                let buttons = new ActionRowBuilder()
                    .addComponents(
                        new ButtonBuilder()
                            .setCustomId(interactions.linking.confirm)
                            .setLabel("Yes")
                            .setStyle(ButtonStyle.Success)
                            .setDisabled(true),
                        new ButtonBuilder()
                            .setCustomId(interactions.linking.deny)
                            .setLabel("No")
                            .setStyle(ButtonStyle.Danger)
                            .setDisabled(true)
                    )
                await interaction.message.delete()
        }
        return;
    }

    if (interaction.isModalSubmit()) {
        switch(interaction.customId) {
            case interactions.applications.modal.ign:
                // If the form is from the pvp application
                // Read their input
                let response = interaction.fields.getTextInputValue(interactions.applications.modal.ign_question);
                // If the player is in the database
                // TODO: get the database stats, to make sure that the player is in the database
                // Create a application
                // Send a message, that they have not been online yet
        }
        return;
    }

    if (interaction.type === 4) {
        if(command.autocomplete) {
            const choices = [];
            await command.autocomplete(interaction, choices);
        }
        return;
    }

    if(!command){
        return client.command.delete(interaction.commandName);
    }

    if(command.cooldown) {
        if(client.cooldown.has(`slash-${command.name}${interaction.user.id}`)){
            return interaction.reply({
                content: "On Cooldown Until: <duration>".replace('<duration>', ms(client.cooldown.get(`slash-${command.name}${interaction.user.id}`) - Date.now(), {long : true}) )
            });
        }

        await command.run(client, interaction);
        client.cooldown.set(`slash-${command.name}${interaction.user.id}`, Date.now() + command.cooldown);
        setTimeout(() => { client.cooldown.delete(`slash-${command.name}${interaction.user.id}`); }, command.cooldown);
    } else {
        await command.run(client, interaction);
    }

    // Applications

}