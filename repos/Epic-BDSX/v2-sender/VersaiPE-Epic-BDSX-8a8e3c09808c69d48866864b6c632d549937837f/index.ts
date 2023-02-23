import { ipfilter } from 'bdsx/core';
import { events } from 'bdsx/event';
import Module from './src/api/module/Module';
import AutoRestartModule from './src/modules/autorestart/AutoRestartModule';
import ClaimsModule from './src/modules/claims/ClaimsModule';
import CombatLoggerModule from './src/modules/combatLogger/CombatLoggerModule';
import ModerationModule from './src/modules/moderation/ModerationModule';
import RanksModule from './src/modules/ranks/RanksModule';
import TPAModule from './src/modules/tpa/TPAModule';
import ServerUtil from './src/util/ServerUtil';
import GangsModule from './src/modules/gangs/GangsModule';
import MainModule from './src/modules/main/MainModule';
import OnlineTime from './src/modules/onlineTime/OnlineTime';

export let sys: IVanillaServerSystem;

events.serverOpen.on(() => {
    sys = server.registerSystem(0, 0);
    ServerUtil.sys = sys;
    const modules: Module[] = [new MainModule, new CombatLoggerModule, new ModerationModule, new RanksModule, new TPAModule, new AutoRestartModule, new GangsModule, new OnlineTime];

    for(let mod of modules) {
        console.log(`Loading Module: ${mod.name}`);
        mod.processEvents();
        mod.processCommands();
    }

    ipfilter.setTrafficLimit(1024 * 1024);
    ipfilter.setTrafficLimitPeriod(60 * 60)
});
