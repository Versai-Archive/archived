import Endpoints from '../network/Endpoints';
import REST from '../network/rest/REST';
import DMChannel from './channels/DMChannel';
import MessageContent from './MessageContent';

export default class User {
	public id: string;
	public username: string;
	public discriminator: string;
	public bot: boolean;
	public avatar: string;

	constructor(data: any) {
		this.id = data.id;
		this.username = data.username;
		this.discriminator = data.discriminator;
		this.bot = !!data.bot;
		this.avatar = data.avatar;
	}

	public get tag() {
		return this.username + '#' + this.discriminator;
	}

	public get avatarURL() {
		return `${Endpoints.CDN_URL}${Endpoints.USER_AVATAR(
			this.id,
			this.avatar
		)}`;
	}

	public async sendDM(content: string | MessageContent) {
		let channel = new DMChannel(
			await REST.request('POST', Endpoints.USER_CHANNELS('@me'), {
				recipients: [this.id],
				type: 1,
			})
		);
		await channel.send(content);
	}
}
