import DiscordPermissions from './Permissions';

export default class Permission {
	public allow: number;
	public deny: number;
	private _json: any;
	constructor(allow: number, deny: number = 0) {
		this.allow = allow;
		this.deny = deny;
	}

	get json() {
		if (!this._json) {
			this._json = {};
			for (const perm of Object.keys(Permissions)) {
				if (!perm.startsWith('all')) {
					// @ts-ignore
					if (this.allow & DiscordPermissions[perm]) {
						this._json[perm] = true;
						// @ts-ignore
					} else if (this.deny & DiscordPermissions[perm]) {
						this._json[perm] = false;
					}
				}
			}
		}
		return this._json;
	}

	/**
	 * Check if this permission allows a specific permission
	 * @arg {String} permission The name of the permission. [A full list of permission nodes can be found on the docs reference page](/Eris/docs/reference)
	 * @returns {Boolean} Whether the permission allows the specified permission
	 */
	has(permission: string) {
		// @ts-ignore
		return !!(this.allow & DiscordPermissions[permission]);
	}
}
