import Guild from './Guild';
import BanMemberOptions from './options/BanMemberOptions';
import Permission from './Permission';
import DiscordPermissions from './Permissions';
import User from './User';

export default class Member {
	public id: string;
	public guild: Guild;
	public user: User;
	public roles: string[];
	public joinedAt: string;
	public premiumSince: string;
	public hoistedRole: string;
	public nick?: string;
	public mute: boolean;
	public deaf: boolean;

	constructor(data: any, guild: Guild) {
		this.id = data.user.id;
		this.guild = guild;
		this.user = new User(data.user);
		this.roles = data.roles;
		this.joinedAt = data.joined_at;
		this.premiumSince = data.premium_since;
		this.hoistedRole = data.hoisted_role;
		this.nick = data.nick;
		this.mute = data.mute;
		this.deaf = data.deaf;
	}

	get permissions() {
		if (this.id === this.guild.ownerID) {
			return new Permission(DiscordPermissions.all);
		} else {
			let perms =
				this.guild.roles.find((r) => r.id === this.guild.id)
					?.permissions.allow || 0;
			for (let role of this.roles) {
				let gRole = this.guild.roles.find((r) => r.id === role);
				if (!gRole) {
					continue;
				}

				const { allow: perm } = gRole.permissions;
				if (perm & DiscordPermissions.administrator) {
					perms = DiscordPermissions.all;
					break;
				} else {
					perms |= perm;
				}
			}
			return new Permission(perms);
		}
	}

	async kick() {
		return await this.guild.kickMember(this.id);
	}

	async ban(o?: BanMemberOptions) {
		return await this.guild.banMember(this.id, o);
	}

	async addRole(roleID: string) {
		return await this.guild.addMemberRole(this.id, roleID);
	}

	async removeRole(roleID: string) {
		return await this.guild.removeMemberRole(this.id, roleID);
	}
}
