import { Message, Zaos } from '../../../../lib';
import { join } from 'path';
import { readdir } from 'fs';
import Command from './Command';

export default class CommandManager {
	private client: Zaos;
	private commandsPath: string = join(
		process.cwd(),
		'/src/bot/src/commands/'
	);

	public commands: Map<string, Command> = new Map();
	constructor(client: Zaos) {
		this.client = client;
	}

	public async run(msg: Message, prefix: string) {
		let args: string | string[] = msg.content
			.slice(prefix.length)
			.trim()
			.split(' ');
		let command = <Command>this.commands.get(args[0]);
		if (!command) return;

		if (command.permissions) {
			let member = msg.member;
			if (!member) return;
			if (!member.permissions.has(command.permissions[0])) return;
		}

		args.shift();
		return await command.run(msg, args);
	}

	public async loadCommands() {
		readdir(this.commandsPath, {}, (err, dirs) => {
			if (err) throw err;
			for (const dir of dirs) {
				readdir(
					`${this.commandsPath}/${dir}`,
					{},
					async (err, commands) => {
						if (err) throw err;
						for (const command of commands) {
							await this.registerCommand(
								command.toString(),
								dir.toString()
							);
						}
					}
				);
			}
		});
	}

	public async registerCommand(fileName: string, dir: string) {
		const _ = await import(`${this.commandsPath}/${dir}/${fileName}`);
		const command = <Command>new _.default(this.client);
		if (!command) throw 'Error in parsing command';
		console.log(`Registered commmand: ${command.name}`);
		this.commands.set(command.name, command);
		if (command.aliases) {
			for (let alias of command.aliases) {
				console.log(`Registered alias: ${alias} (${command.name})`);
				this.commands.set(alias, command);
			}
		}
	}
}
