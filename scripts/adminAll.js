import { client, getArgs } from "./common/client.js";
import colors from 'colors';

const allRepos = await client.request("GET /orgs/{org}/repos", {
    org: 'VersaiPE',
    type: 'all',
    per_page: 100
});

for (let repo of allRepos.data) {
    try {
        await client.request("PUT /orgs/{org}/teams/{team_slug}/repos/{owner}/{repo}", {
            org: 'VersaiPE',
            team_slug: 'admin',
            owner: 'VersaiPE',
            repo: repo.name,
            permission: 'admin'
        });
        console.log(`Added ${colors.green(repo.name)} to admin team.`);
    } catch (e) {
        console.log(`Could not add ${colors.red(repo.name)} to admin team.`);
    }

}