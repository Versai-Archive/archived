import { existsSync, readFileSync, writeFileSync } from "fs";

// Adapted form of: https://github.com/RaptorsMC/Regions/blob/master/src/RegionAPI/Utils/JSON.php

export default class JSONStore<T> {
    public cache: T;
    #path: string;

    public constructor(path: string, array: boolean = true) {
        this.#path = path;
        if(!existsSync(this.#path)) {
            writeFileSync(this.#path, array ? '[]': '{}'); // TODO: error handle here
        }
    }

    public write(contents?: T): void {
        if (!contents) {
            contents = this.cache;
        }

        let cache = JSON.stringify(contents, null, 4);
        writeFileSync(this.#path, cache);
        this.cache = JSON.parse(cache);
    }

    public read(): T {
        return this.cache = JSON.parse(readFileSync(this.#path).toString())
    }
}