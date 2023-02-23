import { Embed, Message } from 'src/lib';
import Application from '../../modules/application/Application';
import Command from '../../modules/commands/Command';

export default class extends Command {
	public name = 'apply';
	public description =
		'A command used to apply for staff/developer/builder on Versai';
	public usage = 'apply <staff | developer |builder>';

	async run(msg: Message, args: string[]) {
		let successful = await Application.createApplication(msg.author);
		if (successful) {
			return await Application.askQuestion(msg);
		} else {
			const em = new Embed()
				.setColor(0x8015ad)
				.setDescription(
					'Please turn on your DMs to create the application'
				);
			return await msg.channel.send({ embed: em.code });
		}
	}
}
