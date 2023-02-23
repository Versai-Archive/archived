export default class Channel {
	public id: string;
	public type: number;
	public createdAt: string;

	constructor(data: any) {
		this.id = data.id;
		this.type = data.type;
		this.createdAt = data.created_at;
	}

	get mention() {
		return `<#${this.id}>`;
	}

	static from(data: any) {
		switch (data.type) {
			case 0:
		}
	}
}
