import PermissionOverwrite from '../PermissionOverwrite';

export default interface CreateChannelOptions {
	name: string;
	type: 0 | 2 | 4 | 5 | 6;
	topic?: string;
	bitrate?: number;
	userLimit?: number;
	rateLimitPerUser?: number;
	position?: number;
	permissionOverwrites?: PermissionOverwrite[];
	parentID?: string;
	nsfw?: boolean;
}
