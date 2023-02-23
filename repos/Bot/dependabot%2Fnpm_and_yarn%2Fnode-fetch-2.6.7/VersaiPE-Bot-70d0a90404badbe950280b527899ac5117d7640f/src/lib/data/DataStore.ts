export default class DataStore<K, V> extends Map<K, V> {
	public flush() {
		for (let key of [...this.keys()]) {
			this.delete(key);
		}
	}

	public getAll() {
		return [...this.values()];
	}
}
