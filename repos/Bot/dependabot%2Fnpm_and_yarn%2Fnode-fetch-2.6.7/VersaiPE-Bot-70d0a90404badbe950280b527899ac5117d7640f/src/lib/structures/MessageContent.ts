import Embed from './Embed';

export default interface MessageContent {
	tts?: boolean;
	type?: number;
	pinned?: boolean;
	embed?: Embed;
	content?: string | Embed;
	attachments?: any[];
}
