const { readdirSync } = require("fs");

module.exports = client => {
  readdirSync("./commands/").forEach(dir => {
    const commands = readdirSync(`./commands/${dir}/`).filter(f => f.endsWith(".js"));

    for (let file of commands) {
      let pull = require(`../commands/${dir}/${file}`);
      let command = new pull();
      if (command.name) client.commands.set(command.name, command);
      else continue;

      if (command.aliases && Array.isArray(command.aliases)) {
        command.aliases.forEach(alias =>
          client.aliases.set(alias, command.name)
        );
      }
    }
  });
};

  /*
fs.readdir("./commands/", (err, files) => {
  if (err) return console.error(err);
  files.forEach((file) => {
    if (!file.endsWith(".js")) return;
    let cmd = require(`./commands/${file}`);
    let cmdFileName = file.split(".")[0];
    client.commands.set(cmd.help.name, cmd);
    if (cmd.help.aliases) {
      cmd.help.aliases.forEach(alias => {
        client.aliases.set(alias, cmd.help.name);
      });
    };
  });
});
*/