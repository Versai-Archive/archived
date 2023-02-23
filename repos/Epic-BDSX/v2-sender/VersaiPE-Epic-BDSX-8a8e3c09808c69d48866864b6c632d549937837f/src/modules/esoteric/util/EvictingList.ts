export default class EvictingList<T> {
    private arr: T[];
    public length: number;

    public constructor(maxSize: number = 20) {
        this.arr = [];
        this.length = maxSize;
    }

    public add(item: T) {
        if (this.arr.length >= this.length) {
            this.arr.shift();
        }
        this.arr.push(item);
        return this;
    }

    public size() {
        return this.arr.length;
    }

    public get(index: number) {
        return this.arr[index];
    }

    public getAll() {
        return this.arr;
    }

    public clear() {
        this.arr = [];
    }
}