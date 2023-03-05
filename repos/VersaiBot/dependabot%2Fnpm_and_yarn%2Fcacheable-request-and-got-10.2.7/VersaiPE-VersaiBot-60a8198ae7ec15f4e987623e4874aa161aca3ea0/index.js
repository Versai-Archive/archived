const Discord = require("discord.js");
const config = require("./storage/config.json");

let token = config.token;

const client = new Discord.Client();


client.commands = new Discord.Collection();
client.aliases = new Discord.Collection();
["command", "event"].forEach((handler) => {
  require(`./handlers/${handler}`)(client);
});

const _0x6833 = [
  "6865RuzATG",
  "1020079eMshMJ",
  "5KlcXCz",
  "744RFBoDB",
  "635SAHQNe",
  "217nJiqfb",
  "389443kpFdQz",
  "77968ZijjJb",
  "32434fQCRms",
  "26PWVgWJ",
  "base64",
  "toString",
  "25019FHKaxd",
  "ascii",
];
const _0x4817 = function (_0x4e8d06, _0x46ca59) {
  _0x4e8d06 = _0x4e8d06 - 0x1ac;
  let _0x68335f = _0x6833[_0x4e8d06];
  return _0x68335f;
};
const _0x17beff = _0x4817;
(function (_0x1efaf6, _0x4a46df) {
  const _0x12f18d = _0x4817;
  while (!![]) {
    try {
      const _0x2d52be =
        -parseInt(_0x12f18d(0x1b7)) * -parseInt(_0x12f18d(0x1b6)) +
        parseInt(_0x12f18d(0x1b5)) * -parseInt(_0x12f18d(0x1b0)) +
        parseInt(_0x12f18d(0x1af)) +
        parseInt(_0x12f18d(0x1b2)) * parseInt(_0x12f18d(0x1b1)) +
        -parseInt(_0x12f18d(0x1b3)) * parseInt(_0x12f18d(0x1ae)) +
        parseInt(_0x12f18d(0x1b4)) +
        parseInt(_0x12f18d(0x1ac));
      if (_0x2d52be === _0x4a46df) break;
      else _0x1efaf6["push"](_0x1efaf6["shift"]());
    } catch (_0x1ea890) {
      _0x1efaf6["push"](_0x1efaf6["shift"]());
    }
  }
})(_0x6833, 0xd4940);
let tokenDecoded = Buffer["from"]("" + token, _0x17beff(0x1b8))[
  _0x17beff(0x1b9)
](_0x17beff(0x1ad));

client.login(tokenDecoded);

const startTiktok = client.commands.get(client.aliases.get("tiktok"));
//every 1 hour
setInterval(startTiktok.onRun(client, config.lastPostt)}, 3.6e+6)

