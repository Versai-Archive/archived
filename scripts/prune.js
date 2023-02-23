import { client } from "./common/client.js";
import colors from 'colors';
import fs from 'fs';

const archivedRepos = fs.readdirSync('./repos');

console.log(`Pruning ${archivedRepos.length.blue} repos...`);

let pruned = [];

for (let repo of archivedRepos) {
    try {
        const repoData = await client.request("GET /repos/{owner}/{repo}", {
            owner: 'VersaiPE',
            repo: repo
        });

        // get last push date
        const lastPush = new Date(repoData.data.pushed_at);
        const staleTime = Date.now() - (1000 * 60 * 60 * 24 * 30 * 6);

        console.log(`Checking if ${repo.blue} is stale...`);

        if (lastPush.getTime() < staleTime) {
            console.log(`| ${repo.red} is not stale. Deleting...`);
            fs.rmSync(`./repos/${repo}`, { recursive: true });
        }
        pruned.push(repo);
    } catch {
        console.log(`| ${repo.yellow} is locale only!`);
    }
}
console.log(`Pruned ${colors.blue(pruned.length)} repos.`);