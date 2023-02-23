import Command from '../command/Command';
import PacketEvent from '../event/PacketEvent';
import Event from '../event/Event';

export default class Module {
    public name: string
    public commands: Command[];
    public events: (Event | PacketEvent)[]

    public constructor(name: string, commands: Command[], events: (Event | PacketEvent)[] ) {
        this.name = name;
        this.commands = commands;
        this.events = events;
    }

    public processCommands(): void {
        for(let cmd of this.commands) {
            cmd.process();
        }
    }

    public processEvents(): void {
        for(let ev of this.events) {
            ev.process();
        }
    }

    public getCommand(name: string): Command | undefined {
        return this.commands.find(c => c.name === name);
    }
}