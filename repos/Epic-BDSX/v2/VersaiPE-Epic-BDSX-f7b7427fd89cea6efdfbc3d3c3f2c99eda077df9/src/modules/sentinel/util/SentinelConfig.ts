const SentinelCheckConfig = {
    Autoclicker: {
        A: {
            code: "Jet",
            enabled: false,
            punishType: "ban",
            maxVL: 5,
            maxCPS: 25
        }
    },

    Fly: {
        A: {
            code: "Wind",
            enabled: true,
            punishType: "ban",
            maxVL: 15
        }
    }
}

const SentinalConfig = {
    alertName: "§l§8[§cSentX§8]"
}

type CheckConfig = {
    code: string;
    enabled: boolean;
    punishType: string;
    maxVL: number;
    [dyn: string]: any
}

export {
    SentinelCheckConfig,
    SentinalConfig,
    CheckConfig
};