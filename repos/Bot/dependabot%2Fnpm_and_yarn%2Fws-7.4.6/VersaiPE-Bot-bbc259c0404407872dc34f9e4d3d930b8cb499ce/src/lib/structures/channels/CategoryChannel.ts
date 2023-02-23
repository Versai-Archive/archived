import Guild from '../Guild';
import GuildChannel from './GuildChannel';

export default class CategoryChannel extends GuildChannel {
	constructor(data: any, guild: Guild) {
		super(data, guild);
	}

	get children(): GuildChannel[] {
		let channels: GuildChannel[] = [];
		if (this.guild && this.guild.channels) {
			for (const channel of this.guild.channels) {
				if (channel.parentID === this.id) {
					channels.push(channel);
				}
			}
		}
		return channels;
	}
}
