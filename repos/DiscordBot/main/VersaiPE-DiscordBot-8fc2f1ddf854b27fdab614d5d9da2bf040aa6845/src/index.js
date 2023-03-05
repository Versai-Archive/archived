const { Client, GatewayIntentBits, Collection, Partials } = require('discord.js');
const { readdirSync } = require("fs-extra");
const { connect, connection } = require("mongoose");
const chalk = require("chalk");
const logger = require("./utils/logger");
const dotenv = require("dotenv");
dotenv.config();

const client = new Client({ // Initializing Discord Client
    partials: [
        Partials.Channel, // Text Channel Partial
        Partials.GuildMember, // Guild Member Partial
        Partials.User, // Discord User Partial
        Partials.Message
    ],
    intents: [
        GatewayIntentBits.Guilds, // Guild Related Bits
        GatewayIntentBits.GuildMembers, // Guild Members Bits
        GatewayIntentBits.GuildIntegrations, // Discord Integrations Bits
        GatewayIntentBits.GuildVoiceStates, // Voice State Bits
        GatewayIntentBits.GuildMessages, // Message's
        GatewayIntentBits.MessageContent,
        GatewayIntentBits.GuildPresences
    ],
});

client.commands = new Collection();
client.cooldown = new Collection();
client.config = require("./storage/config");
client.logger = require("./utils/logger");

readdirSync('./src/handlers').forEach((handler) => {
    require(`./handlers/${handler}`)(client);
});

connection.on("connecting", () => {
    logger.trace(chalk.green(`Connecting to ${chalk.bold("MongoDB!")}`))
});

connection.on("connected", () => {
    logger.trace(chalk.green(`Connected to ${chalk.bold("MongoDB!")}`))
});

client.login(process.env.TOKEN).catch(e => { // Loading Token And Starting Bot
    console.log('Invalid bot token!'); // Error Checking For Invalid Token
    process.exit(0) // Exiting With Status 0 If Token Invalid
});

(async () => {
    await connect(process.env.MONGO_CONNECTION)
        .catch(err => { console.error(err) })
    }
)();