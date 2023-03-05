import { client, getArgs } from "./common/client.js";
import colors from 'colors';
import fs from 'fs';

const ignore = getArgs().includes('--ignore');
if (ignore) {
    console.log('--ignore used. Ignoring stale check.'.red.bold);
}

const allRepos = await client.request("GET /orgs/{org}/repos", {
    org: 'VersaiPE',
    type: 'all',
    per_page: 100
});

// a list of repos archived locally
const archivedRepos = fs.readdirSync('./repos');
// filter out the repos that are still online

for (let repo of allRepos.data) {
    const lastPush = new Date(repo.pushed_at);
    const staleTime = (1000 * 60 * 60 * 24 * 30 * 6);
    const staleDate = new Date(staleTime + lastPush.getTime());
    if (ignore || (Date.now() >= staleDate.getTime())) {
        if (!archivedRepos.includes(repo.name)) {
            if (ignore) {
                console.log(`${'!'.red} ${colors.yellow(repo.name)} is not archived. Run archiveAll.js first.`);
                continue;
            } else {
                console.log(`${'!'.red} ${colors.yellow(repo.name)} is stale, but not archived. Run archiveAll.js first.`);
                continue;
            }
        }
        try {
            await client.request("DELETE /repos/{owner}/{repo}", {
                owner: 'VersaiPE',
                repo: repo.name
            });
            console.log(`${'✓'.green} ${colors.yellow(repo.name)} Deleted`);
        } catch (e) {
            if (archivedRepos.includes(repo.name)) {
                console.log(`${'✓'.yellow} ${colors.reset(repo.name)}`);
            }
            console.log(`${'!'.red.bold} ${colors.yellow(repo.name)} Failed to delete.`);
        }
    } else {
        console.log(`${'✗'.red} ${colors.yellow(repo.name)} not stale.`);
    }
}

console.log(`\n\n${'✓'.green} = Deleted\n${'✓'.yellow} = Archived, but already deleted.\n${'✗'.red} = Not stale.\n${'!'.red} = Could not delete.`);