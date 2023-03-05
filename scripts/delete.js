import { client, getArgs } from "./common/client.js";
import colors from 'colors';
import fs from 'fs';

const name = getArgs()[0];
let forced = false;

if (!name || name === '--force') {
    console.log('Please provide a repo name.'.red);
    process.exit(1);
}

if (getArgs().includes('--force')) {
    console.log('--force used. Ignoring archive check.'.red.bold);
    forced = true;
}

// a list of repos archived locally
const archivedRepos = fs.readdirSync('./repos');
const allRepos = await client.request("GET /orgs/{org}/repos", {
    org: 'VersaiPE',
    type: 'all',
    per_page: 100
});

if (!allRepos.data.find(repo => repo.name === name)) {
    console.log(`Could not find repo ${name}.`.red);
    process.exit(1);
}

if (!archivedRepos.includes(name) && !forced) {
    let cmd = 'pnpm run archive'.blue;
    console.log(`${name} is not archived. Run ${cmd} first.`.red);
    process.exit(1);
}
try {
    await client.request("DELETE /repos/{owner}/{repo}", {
        owner: 'VersaiPE',
        repo: name
    });
    console.log(`${'✓'.green} ${colors.yellow(name)} Deleted`);
} catch (e) {
    if (archivedRepos.includes(name)) {
        console.log(`${'✓'.yellow} ${colors.reset(name)}`);
    }
    console.log(`${'!'.red.bold} ${colors.yellow(name)} Failed to delete.`);
}

console.log(`\n\n${'✓'.green} = Deleted\n${'✓'.yellow} = Archived, but already deleted.\n${'✗'.red} = Not stale.\n${'!'.red} = Could not delete.`);