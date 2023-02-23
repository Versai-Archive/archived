import { Module } from '../..';

export default class EsotericModule extends Module {
    public dataManager: any;
    public settings: any;
    public hasAlerts: any[];
    public constructor() {
        super('esoteric', [], []);
    }
}