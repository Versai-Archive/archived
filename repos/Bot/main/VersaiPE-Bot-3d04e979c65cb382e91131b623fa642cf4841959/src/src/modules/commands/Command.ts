import { Message, Zaos } from '../../../../lib';

export default abstract class Command {
	public abstract name: string;
	public aliases: string[];
	public abstract description: string;
	public abstract usage: string;
	public permissions: string[];

	protected client: Zaos;

	constructor(client: Zaos) {
		this.client = client;
	}

	public abstract async run(msg: Message, args: string[]): Promise<any>;
}
