import Endpoints from '../network/Endpoints';
import REST from '../network/rest/REST';
import CategoryChannel from './channels/CategoryChannel';
import GuildChannel from './channels/GuildChannel';
import TextChannel from './channels/TextChannel';
import VoiceChannel from './channels/VoiceChannel';
import Emoji from './Emoji';
import Member from './Member';
import BanMemberOptions from './options/BanMemberOptions';
import CreateChannelOptions from './options/CreateChannelOptions';
import CreateRoleOptions from './options/CreateRoleOptions';
import Role from './Role';
import VoiceState from './VoiceState';

export default class Guild {
	public id: string;
	public name: string;
	public icon: string;
	public iconHash?: string;
	public splash: string;
	public discoverySplash: string;
	public owner?: boolean;
	public ownerID: string;
	public region: string;
	public afkChannelID: string;
	public afkTimeout: string;
	public widgetEnabled?: boolean;
	public widgetChannelID?: string;
	public verificationLevel: number;
	public defaultMessageNotifications: number;
	public explicitContentFilter: number;
	public roles: Role[];
	public emojis: Emoji[];
	public features: string[];
	public mfaLevel: number;
	public applicationID?: string;
	public systemChannelID?: string;
	public systemChannelFlags: number;
	public rulesChannelID: string;
	public joinedAt: number;
	public large?: boolean;
	public unavailable?: boolean;
	public memberCount: number;
	public voiceStates: VoiceState[];
	public members: Member[];
	public channels: GuildChannel[];
	public vanityURL?: string;
	public description?: string;
	public banner: string;
	/**
	 * Add last few later
	 */

	constructor(data: any) {
		this.id = data.id;
		this.name = data.name;
		this.icon = data.icon;
		this.iconHash = data.icon_hash;
		this.splash = data.splash;
		this.discoverySplash = data.discovery_splash;
		this.owner = data.owner;
		this.ownerID = data.owner_id;
		this.region = data.region;
		this.afkChannelID = data.afk_channel_id;
		this.afkTimeout = data.afk_timeout;
		this.widgetEnabled = data.widget_enabled;
		this.widgetChannelID = data.widget_channel_id;
		this.verificationLevel = data.verification_level;
		this.defaultMessageNotifications = data.default_message_notifications;
		this.explicitContentFilter = data.explicit_content_filter;

		this.features = data.features;
		this.mfaLevel = data.mfa_level;
		this.applicationID = data.application_id;
		this.systemChannelID = data.system_channel_id;
		this.systemChannelFlags = data.system_channel_flags;
		this.joinedAt = data.joined_at;
		this.large = data.large;
		this.unavailable = data.unavailable;
		this.memberCount = data.member_count;
		this.voiceStates = data.voice_states.map((v: any) => new VoiceState(v));
		this.members = data.members.map(
			(member: any) => new Member(member, this)
		);
		this.channels = data.channels.map(
			(ch: any) => new GuildChannel(ch, this)
		);
		this.roles = data.roles.map((r: any) => new Role(r));
		this.emojis = data.emojis.map((e: any) => new Emoji(e));
		this.vanityURL = data.vanity_url_code;
		this.description = data.description;
		this.banner = data.banner;
	}

	get highestRole(): Role {
		return this.roles.sort((a, b) => a.position - b.position)[
			this.roles.length - 1
		];
	}

	get textChannels(): TextChannel[] {
		return <TextChannel[]>this.channels.filter((ch) => ch.type === 0);
	}

	get voiceChannels(): VoiceChannel[] {
		return <VoiceChannel[]>this.channels.filter((ch) => ch.type === 2);
	}

	get categoryChannels(): CategoryChannel[] {
		return <CategoryChannel[]>this.channels.filter((ch) => ch.type === 4);
	}

	async createChannel(o: CreateChannelOptions): Promise<GuildChannel> {
		return new GuildChannel(
			await REST.request('POST', Endpoints.GUILD_CHANNELS(this.id), {
				name: o.name,
				type: o.type,
				topic: o.topic,
				bitrate: o.bitrate,
				user_limit: o.userLimit,
				rate_limit_per_user: o.rateLimitPerUser,
				position: o.position,
				permission_overwrites: o.permissionOverwrites,
				parent_id: o.parentID,
				nsfw: o.nsfw,
			}),
			this
		);
	}

	async editChannel(
		id: string,
		o: CreateChannelOptions
	): Promise<GuildChannel> {
		return new GuildChannel(
			await REST.request('PATCH', Endpoints.CHANNEL(id), {
				name: o.name,
				type: o.type,
				topic: o.topic,
				bitrate: o.bitrate,
				user_limit: o.userLimit,
				rate_limit_per_user: o.rateLimitPerUser,
				position: o.position,
				permission_overwrites: o.permissionOverwrites,
				parent_id: o.parentID,
				nsfw: o.nsfw,
			}),
			this
		);
	}

	async deleteChannel(id: string): Promise<GuildChannel> {
		return new GuildChannel(
			await REST.request('DELETE', Endpoints.CHANNEL(id)),
			this
		);
	}

	async createRole(o: CreateRoleOptions): Promise<Role> {
		return new Role(
			await REST.request('POST', Endpoints.GUILD_ROLES(this.id), o)
		);
	}

	async editRole(id: string, o: CreateRoleOptions): Promise<Role> {
		return new Role(
			await REST.request('PATCH', Endpoints.GUILD_ROLE(this.id, id), o)
		);
	}

	async deleteRole(id: string): Promise<void> {
		await REST.request('DELETE', Endpoints.GUILD_ROLE(this.id, id));
	}

	async editMemberNickname(id: string, nick: string): Promise<void> {
		return await REST.request(
			'PATCH',
			Endpoints.GUILD_MEMBER(this.id, id),
			{ nick }
		);
	}

	async addMemberRole(memberID: string, roleID: string): Promise<void> {
		return await REST.request(
			'PUT',
			Endpoints.GUILD_MEMBER_ROLE(this.id, memberID, roleID)
		);
	}

	async removeMemberRole(memberID: string, roleID: string): Promise<void> {
		return await REST.request(
			'DELETE',
			Endpoints.GUILD_MEMBER_ROLE(this.id, memberID, roleID)
		);
	}

	async kickMember(id: string): Promise<void> {
		return await REST.request(
			'DELETE',
			Endpoints.GUILD_MEMBER(this.id, id)
		);
	}

	async banMember(id: string, o?: BanMemberOptions): Promise<void> {
		return await REST.request('PUT', Endpoints.GUILD_BAN(this.id, id), {
			delete_message_days: o?.deleteMessageDays,
			reason: o?.reason,
		});
	}
}
