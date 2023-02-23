const { readdirSync } = require('fs-extra');
const { mongo, connection } = require('mongoose');

module.exports = (client) => {
    readdirSync('./src/events').forEach(dirs => {
        const events = readdirSync(`./src/events/${dirs}`).filter(files => files.endsWith('.js'));

        if (dirs == "mongo") {
            for (const file of events) {
                const event = require(`../events/${dirs}/${file}`);

                client.on(file.split(".")[0], event.bind(null, connection))
            }
        }

        for (const file of events) {
            const event = require(`../events/${dirs}/${file}`);
            client.logger.info(`Loading event ${file.split(".")[0]}`);
            client.on(file.split(".")[0], event.bind(null, client));
        }


    });
};