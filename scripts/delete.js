import { client } from "./common/client.js";
import colors from 'colors';
import fs from 'fs';

const allRepos = await client.request("GET /orgs/{org}/repos", {
    org: 'VersaiPE',
    type: 'all',
    per_page: 100
});

// a list of repos archived locally
const archivedRepos = fs.readdirSync('./repos');
// filter out the repos that are still online

for (let repo of archivedReposOffline) {
    console.log(` - ${'✓'.yellow} ${repo}`);
}

for (let repo of allRepos.data) {
    const lastPush = new Date(repo.pushed_at);
    const staleTime = (1000 * 60 * 60 * 24 * 30 * 6);
    const staleDate = new Date(staleTime + lastPush.getTime());
    if (Date.now() >= staleDate.getTime()) {
        if (!archivedRepos.includes(repo.name)) {
            console.log(`${'!'.red} ${colors.yellow(repo.name)} is stale, but not archived. Run archiveAll.js first.`);
            continue;
        }
        try {
            await client.request("DELETE /repos/{owner}/{repo}", {
                owner: 'VersaiPE',
                repo: repo.name
            });
            console.log(`${'✓'.green} ${colors.blue(repo.name)}`);
        } catch (e) {
            if (archivedRepos.includes(repo.name)) {
                console.log(`${'✓'.yellow} ${colors.blue(repo.name)}`);
            }
            console.log(`${'!'.red.bold} ${colors.blue(repo.name)} Failed to delete.`);
        }
    } else {
        console.log(` ${'✗'.red} ${repo.name}`);
    }
}

console.log(`\n\n${'✓'.green} = Deleted\n${'✓'.yellow} = Archived, but already deleted.\n${'✗'.red} = Not stale.\n${'!'.red} = Could not delete.`);