import { PlayerPermission } from "bdsx/bds/player";

export default interface CommandSender {
    ign: string;
    permissionLevel: PlayerPermission;

    sendMessage(msg: string): void;
    sendTip(msg: string): void;
}