import { client } from "./common/client.js";
import colors from 'colors';
import fs from 'fs';

const archivedRepos = fs.readdirSync('./repos');

console.log(`Pruning ${archivedRepos.length.blue} repos...`);

let staleOnline = [];

let pruned = [];

for (let repo of archivedRepos) {
    try {
        const repoData = await client.request("GET /repos/{owner}/{repo}", {
            owner: 'VersaiPE',
            repo: repo
        });

        // get last push date
        const lastPush = new Date(repoData.data.pushed_at);
        const staleTime = lastPush.getTime() + (1000 * 60 * 60 * 24 * 30 * 6);

        if (Date.now() < staleTime) {
            console.log(`${repo.red} is not stale. Deleting...`);
            fs.rmSync(`./repos/${repo}`, { recursive: true });
            pruned.push(repo);
        } else {
            staleOnline.push(repoData.data);
            console.log(`${repo.green} is stale!`);
        }
    } catch {
        console.log(`${repo.yellow} is locale only!`);
    }
}
console.log(`Pruned ${colors.blue(pruned.length)} repos.`);

fs.writeFileSync('./stale.md', staleOnline.map(repo => `- [${repo.name}](${repo.html_url})`).join('\n'));