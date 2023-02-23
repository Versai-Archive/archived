export default class Emoji {
	public id: string;
	public name: string;
	public animated: boolean;
	public available: boolean;
	public managed: boolean;
	public roles: string[];
	public requireColons: boolean;

	constructor(data: any) {
		this.id = data.id;
		this.name = data.name;
		this.animated = data.animated;
		this.available = data.available;
		this.managed = data.managed;
		this.roles = data.roles;
		this.requireColons = data.require_colons;
	}
}
