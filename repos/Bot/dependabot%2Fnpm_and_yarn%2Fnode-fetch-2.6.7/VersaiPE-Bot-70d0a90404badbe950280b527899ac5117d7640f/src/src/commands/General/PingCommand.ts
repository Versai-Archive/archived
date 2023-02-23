import { Message } from 'src/lib';
import Command from '../../modules/commands/Command';

export default class extends Command {
	public name = 'ping';
	public aliases = ['pong', 'pung', 'pang'];
	public description = 'A command used to test the latency of VersaiBot';
	public usage = 'ping';

	async run(msg: Message, args: string[]) {
		const m = await msg.channel.send('Pinging...');
		const diff = m.timestamp - msg.timestamp;
		return await m.edit(`Pong! \`${Math.floor(diff)}ms\``);
	}
}
