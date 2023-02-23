import Permission from './Permission';

export default class Role {
	public id: string;
	public name: string;
	public color: number;
	public permissions: Permission;
	public position: number;
	public mentionable: boolean;
	public managed: boolean;
	public hoist: boolean;

	constructor(data: any) {
		this.id = data.id;
		this.name = data.name;
		this.color = data.color;
		this.permissions = new Permission(data.permissions);
		this.position = data.position;
		this.mentionable = data.mentionable;
		this.managed = data.managed;
		this.hoist = data.hoist;
	}
}
