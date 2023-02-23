import { Embed, Message, TextChannel, User, Zaos } from '../../../../lib';
import { questions } from '../../../config.json';

export default class Application {
	public static applications: Map<
		string,
		{
			user: User;
			questions: { question: string; answer: string }[];
			state: number;
		}
	> = new Map();
	private static client: Zaos;

	public static init(client: Zaos) {
		this.client = client;
	}

	public static async createApplication(user: User): Promise<boolean> {
		try {
			const em = new Embed()
				.setColor(0x8015ad)
				.setDescription('**Versai Staff Application**');
			await user.sendDM({ embed: em.code });
			this.applications.set(user.id, { user, questions: [], state: 0 });
			return true;
		} catch (err) {
			return false;
		}
	}

	public static async handleApplication(msg: Message) {
		let app = this.applications.get(msg.author.id);
		if (!app) return;
		app.questions.push({
			question: questions[app.state],
			answer: msg.content,
		});
		app.state++;
		await this.askQuestion(msg);
	}

	public static async askQuestion(msg: Message) {
		let app = this.applications.get(msg.author.id);
		if (!app) return;
		let question = questions[app.state];
		if (!question) {
			return this.finalizeApplication(msg);
		}
		const em = new Embed()
			.setTitle(`Question ${app.state + 1}`)
			.setColor(0x8015ad)
			.setDescription(question);
		return await msg.author.sendDM({ embed: em.code });
	}

	public static async finalizeApplication(msg: Message) {
		let app = this.applications.get(msg.author.id);
		if (!app) return;

		if (msg.content.toLowerCase() === 'yes') {
			this.applications.delete(msg.author.id);
			const em = new Embed()
				.setColor(0x8015ad)
				.setDescription('Sending application to be reviewed');
			await msg.author.sendDM({ embed: em.code });

			const stats = new Embed()
				.setTitle(`${msg.author.tag} | Application`)
				.setColor(0x8015ad)
				.setThumbnail(msg.author.avatarURL, 512, 512);
			app.questions.pop();
			for (let i = 0; i < app.questions.length; i++) {
				stats.addField(
					`**${app.questions[i].question}**`,
					app.questions[i].answer
				);
			}
			let channel = <TextChannel>(
				this.client.data.get('763241476217241601')
			);
			return await channel.send({ embed: stats.code });
		} else if (msg.content.toLowerCase() === 'no') {
			const em = new Embed()
				.setColor(0x8015ad)
				.setDescription('Successfully cancelled application');
			return await msg.author.sendDM({ embed: em.code });
		} else {
			const em = new Embed()
				.setColor(0x8015ad)
				.setDescription(
					'Are you sure you would like to submit your application?\n**[yes/no]**'
				);
			return await msg.author.sendDM({ embed: em.code });
		}
	}

	public static isApplication(msg: Message): boolean {
		if (msg.channel) return false;
		let app = this.applications.get(msg.author.id);
		if (!app) return false;
		else return true;
	}
}
