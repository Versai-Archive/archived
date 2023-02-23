import fetch from 'node-fetch';
import Endpoints from '../Endpoints';

export default class REST {
	private static token: string;
	public static setToken(token: string) {
		this.token = token;
	}

	public static async request(
		method: 'GET' | 'POST' | 'PATCH' | 'DELETE' | 'PUT',
		endpoint: string,
		body?: object
	): Promise<any> {
		let headers: any =
			method === 'POST' || method === 'PATCH'
				? {
						Authorization: `Bot ${this.token}`,
						'User-Agent': 'ZaosLib (API)',
						'Content-Type': 'application/json',
				  }
				: {
						Authorization: `Bot ${this.token}`,
						'User-Agent': 'ZaosLib (API)',
				  };

		let res = await fetch(Endpoints.BASE_URL + endpoint, {
			method: method,
			body: JSON.stringify(body),
			headers: headers,
		});
		if (!res.ok) {
			throw 'Failed POST: ' + (await res.text());
		} else {
			if (res.status === 204) {
				return;
			} else {
				return res.json();
			}
		}
	}
}
