import { client } from "./common/client.js";
import colors from 'colors';
import fs from 'fs';

const allRepos = await client.request("GET /orgs/VersaiPE/repos?type=all&per_page=100", {
    org: 'VersaiPE'
});


console.log(`Found ${allRepos.data.length} repos (online).`);
console.log(' - ' + allRepos.data.map(repo => {
    if (fs.existsSync(`./repos/${repo.name}`)) {
        return `${'✓'.green} ${repo.name} (${colors.blue(repo.html_url)})`;
    } else {
        return `${'✗'.red} ${repo.name} (${colors.blue(repo.html_url)})`;
    }
}).join('\n - '));

// a list of repos archived locally
const archivedRepos = fs.readdirSync('./repos');
// filter out the repos that are still online
const archivedReposOffline = archivedRepos.filter(repo => !allRepos.data.find(onlineRepo => onlineRepo.name === repo));

for (let repo of archivedReposOffline) {
    console.log(` - ${'✓'.yellow} ${repo}`);
}

console.log(`\n\n${'✓'.green} = Already archived\n${'✓'.yellow} = Archived, but deleted.\n${'✗'.red} = Not archived.`);