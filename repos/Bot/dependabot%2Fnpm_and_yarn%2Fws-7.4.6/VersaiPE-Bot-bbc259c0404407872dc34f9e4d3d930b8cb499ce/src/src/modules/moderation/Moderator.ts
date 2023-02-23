import { Embed, Member, TextChannel, Zaos } from '../../../../lib';

export default class Moderator {
	private static client: Zaos;
	public static init(client: Zaos) {
		this.client = client;
	}

	/**
	 * Wrapper function for kicking a user
	 *
	 * **Note:**
	 * This check is meant to be used in a command where the permissions have already been handled
	 * @param channel Channel you want to kick a user in
	 * @param id ID of user/member you want to kick
	 * @param reason Reason for kick
	 */
	public static async kick(
		channel: TextChannel,
		id: string,
		reason: string = 'Not Specified'
	) {
		let member = channel.guild.members.find((m) => m.id === id);
		if (!member) return; // We can make this verbose
		await this.postTask('kick', channel, member, reason).catch();
		return await member.kick();
	}

	/**
	 * Wrapper function for banning a user
	 *
	 * **Note:**
	 * This check is meant to be used in a command where the permissions have already been handled
	 * @param channel Channel you want to ban a user in
	 * @param id ID of user/member you want to ban
	 * @param reason Reason for ban
	 */
	public static async ban(
		channel: TextChannel,
		id: string,
		reason: string = 'Not Specified'
	) {
		let member = channel.guild.members.find((m) => m.id === id);
		if (!member) return; // We can make this verbose
		await this.postTask('ban', channel, member, reason).catch();
		return await member.ban();
	}

	/**
	 * A post task that handles a post punishment facilitation
	 * @param type Type of punishment that was dealt
	 * @param member Member that received the punishment
	 */
	public static async postTask(
		type: 'kick' | 'ban' | 'mute',
		ch: TextChannel,
		member: Member,
		reason: string = 'Not Specified'
	) {
		const em = new Embed()
			.setTitle(`Issued Punishment | ${type.toUpperCase()}`)
			.setColor(0x8015ad)
			.setThumbnail(member.user.avatarURL, 256, 256)
			.addField('**Member punished:**', member.user.tag)
			.addField('**Reason:**', reason);
		await ch.send({ embed: em.code });

		try {
			const userEm = new Embed()
				.setColor(0x8015ad)
				.setTitle('Issued Punishment')
				.addField('**Type:**', type)
				.addField('**Reason:**', reason);
			await member.user.sendDM({ embed: userEm });

			const em = new Embed()
				.setColor(0x8015ad)
				.setDescription('Successfully sent DM punished user');
			return await ch.send({ embed: em.code });
		} catch (err) {
			const em = new Embed()
				.setColor(0x8015ad)
				.setDescription('Failed to DM punished user');
			return await ch.send({ embed: em.code });
		}
	}
}
