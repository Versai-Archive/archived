import Guild from '../Guild';
import CreateChannelOptions from '../options/CreateChannelOptions';
import PermissionOverwrite from '../PermissionOverwrite';
import Channel from './Channel';
import TextChannel from './TextChannel';

export default class GuildChannel extends Channel {
	public guild: Guild;
	public name: string;
	public guildID: string;
	public position: number;
	public permissionOverwrites: PermissionOverwrite[];
	public parentID: string;

	constructor(data: any, guild: Guild) {
		super(data);
		this.guild = guild;
		this.name = data.name;
		this.guildID = data.guild_id;
		this.position = data.position;
		this.permissionOverwrites = data.permission_overwrites.map(
			(p: any) => new PermissionOverwrite(p)
		);
		this.parentID = data.parent_id;
	}

	async edit(o: CreateChannelOptions): Promise<GuildChannel> {
		return await this.guild.editChannel(this.id, o);
	}

	async delete(): Promise<GuildChannel> {
		return await this.guild.deleteChannel(this.id);
	}
}
