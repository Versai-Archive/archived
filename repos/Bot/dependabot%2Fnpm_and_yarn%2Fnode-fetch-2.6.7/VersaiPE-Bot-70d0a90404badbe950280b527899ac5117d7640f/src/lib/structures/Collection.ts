export default class Collection<T> extends Array<T> {
	public base: { id: string };
	constructor(base: { id: string }) {
		super();
		this.base = base;
	}
}
