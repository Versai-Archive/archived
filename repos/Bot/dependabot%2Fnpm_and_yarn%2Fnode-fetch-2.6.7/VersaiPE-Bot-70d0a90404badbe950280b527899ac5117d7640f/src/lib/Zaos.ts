import EventEmitter from 'eventemitter3';
import DataStore from './data/DataStore';
import WSM from './network/gateway/WSM';
import REST from './network/rest/REST';
import CategoryChannel from './structures/channels/CategoryChannel';
import Channel from './structures/channels/Channel';
import DMChannel from './structures/channels/DMChannel';
import GuildChannel from './structures/channels/GuildChannel';
import TextChannel from './structures/channels/TextChannel';
import VoiceChannel from './structures/channels/VoiceChannel';
import Guild from './structures/Guild';
import User from './structures/User';

type DataStoreValue =
	| Guild
	| GuildChannel
	| TextChannel
	| DMChannel
	| VoiceChannel
	| CategoryChannel
	| Channel;

export default class Zaos extends EventEmitter {
	private token: string;
	private wsm: WSM;
	public user!: User;

	public data: DataStore<string, DataStoreValue>;

	constructor(token: string) {
		super();
		this.token = token;
		this.data = new DataStore();
		this.wsm = new WSM(this.token, this);
		REST.setToken(this.token);
		this.wsm.connect();
	}
}
