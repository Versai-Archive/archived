/* eslint-disable no-restricted-imports */
import { CustomForm, Form, FormLabel } from "bdsx/bds/form";
import { command } from "bdsx/command";
import { __values } from "tslib";
import { log } from "..";
import { serverInstance } from "../../../bdsx/bds/server";
import { CANCEL } from "../../../bdsx/common";
import { webhook } from "../utils/webhook";

command.register("report", "Report a player!").overload(async(param, origin, output) => {
    const sender = origin.getEntity();
    if (sender === null) {
        log("this is a player only command");
        return;
    }
    const ni = sender.getNetworkIdentifier();

    let playerIgns = serverInstance.minecraft.getLevel().players.toArray().map(p => p.getName());


    const isYes = await Form.sendTo(ni, {
        type: 'modal',
        title: '§cReport a player?',
        content: 'If you would like to report a player press "§l§aYes§r" if not press "§l§cNo§r"',
        button1: '§l§aYes',
        button2: '§l§cNo',
    });
    if (isYes) {
        const res = await Form.sendTo(ni, {
            type: 'custom_form',
            title: '§4Report Player',
            content: [
                {
                    type: 'label',
                    text: '§cThis is a form to report a player for cheating and will be sent to staff and a member will get with you shortly!! Please §4ONLY §cuse this form if it is necessary!'
                },
                {
                    type: 'dropdown', //dropdown menu
                    text: 'Player',
                    options: playerIgns,
                    default: 0
                },
                {
                    type: 'input', //text
                    text: 'Reason(s)',
                    placeholder: 'Reason(s)',
                    default: ''
                },
            ]
        });
        if (res === null) return; // x pressed

        for (let i=0;i<res.length;i++) {
            if (`${playerIgns[i]}` === undefined) {
                return;
            } else {
                log(`Suspect - ${playerIgns[i]}`)
            } if else (`${res[i]}` === null) {
                CANCEL;
            } else {
                log(`Reason - ${res[i]}`)
            }
            log(`Reported by - ${sender.getName()}`);
        }
    }
}, {});
