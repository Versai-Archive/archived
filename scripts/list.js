import { client } from "./common/client.js";
import colors from 'colors';
import fs from 'fs';

const allRepos = await client.request("GET /orgs/VersaiPE/repos?type=all&per_page=100", {
    org: 'VersaiPE'
});


console.log(`Found ${allRepos.data.length} repos.`);
console.log(' - ' + allRepos.data.map(repo => {
    if (fs.existsSync(`./repos/${repo.name}`)) {
        return `${'✓'.green} ${repo.name} (${colors.blue(repo.html_url)})`;
    } else {
        return `${'✗'.red} ${repo.name} (${colors.blue(repo.html_url)})`;
    }
}).join('\n - '));

console.log(`\n\n${'✓'.green} = Already archived, ${'✗'.red} = Not archived.`);