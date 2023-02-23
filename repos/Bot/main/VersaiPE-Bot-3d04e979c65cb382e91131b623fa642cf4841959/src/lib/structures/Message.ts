import { DMChannel } from '..';
import Endpoints from '../network/Endpoints';
import REST from '../network/rest/REST';
import TextChannel from './channels/TextChannel';
import Embed from './Embed';
import Guild from './Guild';
import Member from './Member';
import MessageContent from './MessageContent';
import User from './User';

export default class Message {
	public id: string;
	public type: number;
	public tts: boolean;
	public timestamp: number;
	public pinned: boolean;
	public nonce: string | number;
	public mentions: User[];
	public flags: any;
	public embeds: Embed[];
	public editedTimestamp: any;
	public content: string;
	private guildID: string;
	public guild: Guild;
	private channelID: string;
	public channel: TextChannel | DMChannel;
	public author: User;
	public member?: Member;
	public attachments: any[];

	constructor(data: any, channel?: TextChannel | DMChannel, guild?: Guild) {
		this.id = data.id;
		this.type = data.type;
		this.tts = data.tts || false;
		this.timestamp = Date.parse(data.timestamp) || Date.now();
		this.pinned = data.pinned || false;
		this.nonce = data.nonce || null;
		this.mentions = data.mentions.map((m: any) => new User(m));
		this.flags = data.flags;
		this.embeds = data.embeds;
		this.content = data.content;
		this.guildID = data.guild_id;
		if (guild) this.guild = guild;
		this.channelID = data.channel_id;
		if (channel) this.channel = channel;
		this.author = new User(data.author);
		this.member = this.guild?.members.find((m) => m.id === this.author.id);
		this.attachments = data.attachments;
	}

	async reply(content: string): Promise<Message> {
		return await this.channel.send(`<@${this.author.id}>, ${content}`);
	}

	async edit(content: string | MessageContent): Promise<Message> {
		if (typeof content === 'string') {
			content = { content: content };
		}
		return new Message(
			await REST.request(
				'PATCH',
				Endpoints.CHANNEL_MESSAGE(this.channel.id, this.id),
				content
			),
			this.channel,
			this.guild
		);
	}

	async delete(): Promise<void> {
		return await REST.request(
			'DELETE',
			Endpoints.CHANNEL_MESSAGE(this.channel.id, this.id)
		);
	}
}
