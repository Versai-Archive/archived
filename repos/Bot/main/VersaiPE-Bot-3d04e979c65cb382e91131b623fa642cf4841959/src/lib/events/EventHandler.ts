import Payload from '../network/interfaces/Payload';
import CategoryChannel from '../structures/channels/CategoryChannel';
import Channel from '../structures/channels/Channel';
import DMChannel from '../structures/channels/DMChannel';
import GuildChannel from '../structures/channels/GuildChannel';
import TextChannel from '../structures/channels/TextChannel';
import VoiceChannel from '../structures/channels/VoiceChannel';
import Guild from '../structures/Guild';
import Message from '../structures/Message';
import User from '../structures/User';
import Zaos from '../Zaos';

export default class EventHandler {
	private client: Zaos;

	constructor(client: Zaos) {
		this.client = client;
	}

	public handleEvent(payload: Payload) {
		let fn =
			'on' +
			(<string>payload.t)
				.toLowerCase()
				.split('_')
				.map((e) => e.charAt(0).toUpperCase() + e.substr(1))
				.join('');
		if ((this as any)[fn] !== undefined) {
			return (this as any)[fn](payload.d);
		}
	}

	public onReady(data: any) {
		this.client.user = new User(data.user);
		this.client.emit('ready');
	}

	public onGuildCreate(data: any) {
		const guild = new Guild(data);
		for (let channel of data.channels) {
			channel.guild_id = data.id;
			this.onChannelUpdate(channel);
		}
		this.client.data.set(guild.id, guild);
		this.client.emit('guildCreate', guild);
	}

	public onMessageCreate(data: any) {
		let message, channel: TextChannel | DMChannel, guild: Guild;
		guild = <Guild>this.client.data.get(data.guild_id);
		channel = <TextChannel | DMChannel>(
			this.client.data.get(data.channel_id)
		);
		if (channel instanceof TextChannel) {
			message = new Message(data, channel, guild);
		} else {
			message = new Message(data, channel);
		}

		this.client.emit('messageCreate', message);
	}

	public onChannelUpdate(data: any) {
		let channel: Channel;
		let guild: Guild;
		if (data.id === '767882533492752412') console.log(data);
		switch (data.type) {
			case 0:
			case 5:
				guild = <Guild>this.client.data.get(data.guild_id);
				channel = new TextChannel(data, guild);
				this.client.data.set(channel.id, channel);
				return;
			case 2:
				guild = <Guild>this.client.data.get(data.guild_id);
				channel = new VoiceChannel(data, guild);
				this.client.data.set(channel.id, channel);
				return;
			case 4:
				guild = <Guild>this.client.data.get(data.guild_id);
				channel = new CategoryChannel(data, guild);
				this.client.data.set(channel.id, channel);
				return;
			case 1:
				channel = new DMChannel(data);
				this.client.data.set(channel.id, channel);
				return;
			default:
				guild = <Guild>this.client.data.get(data.guild_id);
				channel = new Channel(data);
				this.client.data.set(channel.id, channel);
				return;
		}
	}
}
