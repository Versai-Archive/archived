import Guild from '../Guild';
import GuildChannel from './GuildChannel';

export default class VoiceChannel extends GuildChannel {
	public bitrate: number;
	public userLimit: number;

	constructor(data: any, guild: Guild) {
		super(data, guild);
		this.bitrate = data.bitrate;
		this.userLimit = data.user_limit;
	}
}
