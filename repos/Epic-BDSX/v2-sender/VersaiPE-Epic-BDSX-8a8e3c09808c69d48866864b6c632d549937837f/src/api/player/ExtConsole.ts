// "This is being used for commands to send messags to consoles and not just throw an error"
import { DimensionId } from "bdsx/bds/actor";
import { Vec3 } from "bdsx/bds/blockpos";
import { CommandPermissionLevel } from "bdsx/bds/command";
import { NetworkIdentifier } from "bdsx/bds/networkidentifier";
import { Packet } from "bdsx/bds/packet";
import { PlayerPermission, ServerPlayer } from "bdsx/bds/player";
import Sender from "../command/Sender";
import ExtPlayer from "./ExtPlayer";

export default class ExtConsole implements Sender {
    public readonly ign = "CONSOLE";
    public readonly permissionLevel = PlayerPermission.OPERATOR;

    sendMessage(msg: string): void {
        console.log(`[COMMAND] ${msg}`)
    }

    public sendTip(msg: string): void {
        console.log(`[TIP] ${msg}`)
    }
}