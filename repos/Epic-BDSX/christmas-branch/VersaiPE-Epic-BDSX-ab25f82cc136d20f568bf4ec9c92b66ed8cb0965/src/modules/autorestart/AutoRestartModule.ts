import { bedrock_server_exe } from "bdsx/core";
import { bedrockServer } from "bdsx/launcher";
import { Module } from "../..";
import FMT from "../../util/FMT";
import ServerUtil from "../../util/ServerUtil";

export default class AutoRestartModule extends Module {
    public static readonly TICK_MILLISECOND = 10000; // 10 seconds
    //public static readonly CLEAN_TICK = 90; // 15 minutes
    public static readonly RESTART_TICK = 180; // 30 minutes
    public static CURRENT_TICK = 0;

    private static interval: NodeJS.Timeout;

    public constructor() {
        super("autorestart", [], []);

        AutoRestartModule.call();
    }

    private static call() {
       this.interval = setInterval(() => {
        this.CURRENT_TICK = this.CURRENT_TICK + 1;

            switch (this.CURRENT_TICK) {
                case this.RESTART_TICK - 1: {
                    ServerUtil.broadcastMessage(FMT.AQUA + "The server is restarting in 10 seconds!");
                    break;
                }

                case this.RESTART_TICK - 10: {
                    ServerUtil.broadcastMessage(FMT.AQUA + "The server is restarting in 10 minutes!");
                    break;
                }
            }

            //switch (this.CURRENT_TICK) {
            //    case this.CLEAN_TICK - 1: {
            //        ServerUtil.broadcastMessage(FMT.LIGHT_PURPLE + 'All items on the ground will be cleared in 10 seconds!');
            //        break;
            //    }

            //    case this.CURRENT_TICK - 10: {
            //        ServerUtil.broadcastMessage(FMT.LIGHT_PURPLE + 'All items on the ground will be cleared in 10 minutes!');
            //        break;
            //    }
            //}

            if (this.CURRENT_TICK >= this.RESTART_TICK) {
                bedrockServer.stop();
                this.reset();
            }

            //if (this.CURRENT_TICK >= this.CLEAN_TICK) {
               // bedrockServer.executeCommand("kill @e[type=item]")
            //}

        }, this.TICK_MILLISECOND);
    }

    public static reset() {
        if (this.interval) {
            clearInterval(this.interval);
        }
    }
}