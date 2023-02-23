import { Module } from "../..";

export default class SentinelModule extends Module {
    public static violations: Map<string, number>;

    public constructor() {
        super('sentinel', [], []);
        SentinelModule.violations = new Map();
    }
}