export default class PermissionOverwrite {
	public id: string;
	public type: 'role' | 'member';
	public allow: number;
	public deny: number;

	constructor(data: any) {
		this.id = data.id;
		this.type = data.type;
		this.allow = data.allow;
		this.deny = data.deny;
	}
}
