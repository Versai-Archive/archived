const chalk = require("chalk");

class logger {

    info(value) {
        console.log(chalk.bgBlueBright(`[INFO]`) + chalk.reset() + " " + chalk.whiteBright(value))
    }

    debug(value) {
        console.log(chalk.bgGray(`[DEBUG]`) + chalk.reset() + " " + chalk.whiteBright(value))
    }

    error(value) {
        console.log(chalk.bgRedBright(chalk.grey(`[ERROR]`)) + chalk.reset() + " " + chalk.whiteBright(value))
    }

    warning(value) {
        console.log(chalk.bgYellowBright(`[WARNING]`) + chalk.reset() + " " + chalk.whiteBright(value))
    }

    trace(value) {
        console.log(chalk.bgGreenBright(`[TRACE]`) + chalk.reset() + " " + chalk.whiteBright(value))
    }

    fatal(value) {
        console.log(chalk.bgRed(`[FATAL]`) + chalk.reset() + " " + chalk.whiteBright(value))
    }

}

module.exports = new logger();