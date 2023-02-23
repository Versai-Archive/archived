import Logger from '../../utils/Logger';
import WebSocket from 'ws';
import Zaos from '../../Zaos';
import Endpoints from '../Endpoints';
import Payload from '../interfaces/Payload';
import OPCodes from '../interfaces/OPCodes';
import EventHandler from '../../events/EventHandler';

export default class WSM {
	private token: string;
	private client: Zaos;

	private eventHandler: EventHandler;

	private heartbeat: number;

	private logger: Logger = new Logger('WSM');

	private ws: WebSocket;

	constructor(token: string, client: Zaos) {
		this.token = token;
		this.client = client;

		this.eventHandler = new EventHandler(this.client);
	}

	async connect() {
		this.ws = new WebSocket(Endpoints.GATEWAY);

		this.ws.on('open', () => this.onOpen());

		this.ws.on('message', (data: WebSocket.Data) => this.onMessage(data));
	}

	onOpen() {
		this.logger.debug('Opened websocket connection...');
	}

	onMessage(data: WebSocket.Data) {
		const payload = <Payload>JSON.parse(data.toString());

		this.logger.debug(`Received Op Code: ${payload.op}`);

		switch (payload.op) {
			case OPCodes.HELLO: {
				this.createHeartbeat(payload.d.heartbeat_interval || 45000);
				return this.ws.send(
					Buffer.from(
						JSON.stringify({
							op: OPCodes.IDENTIFY,
							d: {
								token: this.token,
								properties: {
									$os: process.platform,
									$browser: 'Zaos-Lib',
									$device: 'Zaos-Lib',
								},
							},
						})
					)
				);
			}
			case OPCodes.DISPATCH: {
				return this.eventHandler.handleEvent(payload);
			}
		}
	}

	createHeartbeat(heartbeat: number) {
		if (this.heartbeat) {
			throw 'Already active heartbeat';
		} else {
			this.heartbeat = heartbeat;
			setInterval(() => {
				this.ws.send(
					Buffer.from(
						JSON.stringify({ op: OPCodes.HEARTBEAT, d: null })
					)
				);
			}, heartbeat);
		}
	}
}
