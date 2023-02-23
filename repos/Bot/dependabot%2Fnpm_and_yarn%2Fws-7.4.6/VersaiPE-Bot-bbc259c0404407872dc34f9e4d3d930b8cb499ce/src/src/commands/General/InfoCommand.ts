import { Embed, Message } from '../../../../lib';
import Command from '../../modules/commands/Command';

export default class extends Command {
	public name = 'info';
	public aliases = ['inf', 'botinfo'];
	public description = 'A command used to get info on VersaiBot';
	public usage = 'info';

	async run(msg: Message, args: string[]) {
		const em = new Embed()
			.setTitle('VersaiBot')
			.setColor(0x8015ad)
			.addField('**Developer:**', 'Cqdet#3941 (522895569039917066)')
			.addField('**Language:**', 'TypeScript')
			.addField('**Library:**', 'ZaosLib')
			.addField('**Member Count:**', msg.guild.memberCount.toString())
			.setFooter(
				`Command request by: ${msg.member?.user.tag}`,
				msg.member?.user.avatarURL || ''
			);
		return await msg.channel.send({ embed: em.code });
	}
}
