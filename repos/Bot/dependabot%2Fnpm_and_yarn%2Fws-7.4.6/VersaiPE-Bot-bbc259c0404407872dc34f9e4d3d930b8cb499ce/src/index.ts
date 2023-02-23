import { DMChannel, Embed, Message, Zaos } from '../lib';
import CommandManager from './src/modules/commands/CommandManager';
import Application from './src/modules/application/Application';
import { token } from './config.json';
const client = new Zaos(token);

const commandManager = new CommandManager(client);

client.on('ready', async () => {
	console.log(`Versai Bot is on: ${client.user.tag}`);
	await commandManager.loadCommands();
	Application.init(client);
});

client.on('messageCreate', async (msg: Message) => {
	if (msg.author.bot) return;
	let prefix = '!!';
	if (Application.isApplication(msg)) {
		return await Application.handleApplication(msg);
	}

	if (msg.content.startsWith(prefix)) {
		commandManager.run(msg, prefix);
	}
});
