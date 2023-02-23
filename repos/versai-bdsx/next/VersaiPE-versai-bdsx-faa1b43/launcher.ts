
// launcher.ts is the launcher for BDS
// These scripts are run before launching BDS
// So there is no 'server' variable yet
// launcher.ts will import ./index.ts after launching BDS.

// install source map
import { install as installSourceMapSupport, remapAndPrintError } from "bdsx/source-map-support";
installSourceMapSupport();

import { disable, green, red } from 'colors';

if(process.env.COLOR && !(process.env.COLOR === 'true' || process.env.COLOR === 'on')) disable();

// check
import 'bdsx/common';
import 'bdsx/checkcore';
import 'bdsx/checkmodules';
import 'bdsx/permissions';

// install bdsx error handler
import { installErrorHandler } from "bdsx/errorhandler";
installErrorHandler();

// imports
require('bdsx/legacy');

import { installMinecraftAddons } from 'bdsx/addoninstaller';
import { bedrockServer } from "bdsx/launcher";
import { loadAllPlugins } from "bdsx/plugins";

import { events } from "bdsx/event";

console.log(
"  _____      _____ \n".green +
"  \\    \\    /    / \n".green +
"   \\".green + "___ ".white + "\\".green + "__".white + "/".green + " ___".white + "/  \n".green +
"   | _ )   \\/ __|  \n".white +
"   | _ \\ |) \\__ \\  \n".white +
"   |___/___/|___/  \n".white +
"   /    /  \\    \\  \n".green +
"  /____/    \\____\\ \n".green
);

(async() => {
    events.serverClose.on(()=>{
        console.log(red(`[BDSX] Server has closed`));
        setTimeout(async () => {
            await bedrockServer.launch();
            console.log(green("[BDSX] Restarting Server..."));
            bedrockServer.DefaultStdInHandler.install();
            require('./index');
        }, 3000).unref();
    });

    await Promise.all([
        loadAllPlugins(),
        installMinecraftAddons()
    ]);

    await bedrockServer.launch();
    bedrockServer.DefaultStdInHandler.install();
    require('./index');
})().catch(remapAndPrintError);
