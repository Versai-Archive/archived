import Endpoints from '../../network/Endpoints';
import REST from '../../network/rest/REST';
import Embed from '../Embed';
import Guild from '../Guild';
import Message from '../Message';
import MessageContent from '../MessageContent';
import GuildChannel from './GuildChannel';

export default class TextChannel extends GuildChannel {
	public topic: string;
	public rateLimitPerUser: number;
	public lastMessageID: string;
	constructor(data: any, guild: Guild) {
		super(data, guild);
		this.topic = data.topic;
		this.rateLimitPerUser = data.rate_limit_per_user;
		this.lastMessageID = data.last_message_id;
	}

	async send(content: string | MessageContent | Embed): Promise<Message> {
		if (typeof content === 'string') {
			content = { content: content };
		}
		return new Message(
			await REST.request(
				'POST',
				Endpoints.CHANNEL_MESSAGES(this.id),
				content
			),
			this,
			this.guild
		);
	}

	async getMessage(id: string): Promise<Message> {
		return new Message(
			await REST.request('GET', Endpoints.CHANNEL_MESSAGE(this.id, id)),
			this,
			this.guild
		);
	}

	async getMessages(limit: number): Promise<Message[]> {
		let res = await REST.request(
			'GET',
			Endpoints.CHANNEL_MESSAGES(this.id),
			{
				limit: limit,
			}
		);
		return res.map((r: any) => new Message(r, this, this.guild));
	}

	async deleteMessage(id: string) {
		return await REST.request(
			'DELETE',
			Endpoints.CHANNEL_MESSAGE(this.id, id)
		);
	}

	async deleteMessages(ids: string[]) {
		return await REST.request(
			'DELETE',
			Endpoints.CHANNEL_BULK_DELETE(this.id),
			{
				messages: ids,
			}
		);
	}
}
