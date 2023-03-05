const config = require('../storage/config.json');

module.exports = (client, msg) => {

  if (msg.author.bot) return;
  if (!msg.content.startsWith(config.prefix)) return;
  if (!msg.guild) return;
  
  let args = msg.content.slice(config.prefix.length).trim().split(/[^\S\n]+/g);
  let cmd = args.shift().toLowerCase();
  let command = client.commands.get(cmd);

  if (cmd.length === 0) return;
  if (!command) command = client.commands.get(client.aliases.get(cmd));
  if (command) command.onRun(client, msg, args);
  else return;
}