/**
 * this is for in the future if we decide to enable infinite levels
 */

import GangsModule from "../../../../modules/gangs/GangsModule"
import { GangData } from "../../../../modules/gangs/types/Types";
import JSONStore from "../../../JSONStore";

export const Level1 = 1

export default class LevelsModule {
    public static store: JSONStore<GangData[]>;

    public levelUp(gang:string) {
        const data = LevelsModule.store.read()
        let level = data.find(a => a.level)?.level;
        if (!level) return;
        if (level?.valueOf() > 10){
            const newLevel = level + 1
            const newXp = level * 10 + 1000 / .50;
        }
        const newLevel = level += 1 / .25
    }
}