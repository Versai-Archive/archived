import chalk from 'chalk';

export default class Logger {
	public static DEBUG_ENABLED: boolean = false;
	private name: string;

	constructor(name: string) {
		this.name = name;
	}

	public debug(msg: string) {
		if (Logger.DEBUG_ENABLED)
			console.log(chalk.grey(`[${this.name}/DEBUG]: ${msg}`));
	}

	public info(msg: string) {
		console.log(chalk.green(`[${this.name}/INFO]: ${msg}`));
	}

	public notice(msg: string) {
		console.log(chalk.blue(`[${this.name}/NOTICE]: ${msg}`));
	}

	public warn(msg: string) {
		console.log(chalk.red(`[${this.name}/WARN]: ${msg}`));
	}

	public error(msg: string) {
		console.log(chalk.redBright(`[${this.name}/ERROR]: ${msg}`));
	}
}
