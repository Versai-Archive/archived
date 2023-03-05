const { PermissionsBitField } = require('discord.js');
const { readdirSync } = require("fs-extra");
const { Routes } = require('discord-api-types/v9');
const { REST } = require('@discordjs/rest');
const dotenv = require("dotenv");
dotenv.config();


module.exports = (client) => {

    const slashCommands = [];

    readdirSync('./src/commands').forEach(async dir => {
        const files = readdirSync(`./src/commands/${dir}/`).filter(file => file.endsWith('.js'));

        files.forEach(async (file) => {
            const slashCommand = require(`../commands/${dir}/${file}`);

            slashCommands.push({
                name: slashCommand.name,
                description: slashCommand.description,
                type: slashCommand.type,
                options: slashCommand.options ? slashCommand.options : null,
                default_permission: slashCommand.default_permission ? slashCommand.default_permission : null,
                default_member_permissions: slashCommand.default_member_permissions ? PermissionsBitField.resolve(slashCommand.default_member_permissions).toString() : null
            });

            client.logger.info(`Loading /${slashCommand.name}`);
            await client.commands.set(slashCommand.name, slashCommand)
        })

    });

    (async () => {
        await new REST().setToken(process.env.TOKEN).put(
                Routes.applicationCommands(process.env.CLIENT_ID),
            { body: slashCommands }
        );

    })();

};