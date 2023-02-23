import * as dotenv from "dotenv";
import { Octokit } from "octokit";

dotenv.config();

export const client = new Octokit({ auth: process.env.TOKEN });

export function getArgs() {
    return process.argv.slice(2);
}