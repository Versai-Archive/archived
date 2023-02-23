import Endpoints from '../../network/Endpoints';
import REST from '../../network/rest/REST';
import Message from '../Message';
import MessageContent from '../MessageContent';
import Channel from './Channel';

export default class DMChannel extends Channel {
	constructor(data: any) {
		super(data);
	}

	async send(content: string | MessageContent) {
		if (typeof content === 'string') {
			content = { content: content };
		}
		return new Message(
			await REST.request(
				'POST',
				Endpoints.CHANNEL_MESSAGES(this.id),
				content
			)
		);
	}
}
