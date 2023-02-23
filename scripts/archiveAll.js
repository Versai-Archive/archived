import { client, getArgs } from "./common/client.js";
import colors from 'colors';
import fs from 'fs';
import decompress from 'decompress';
import Confirm from 'prompt-confirm';

const prompt = new Confirm(`Are you sure you want to archive all repos?`);

if (await prompt.run() === false) {
    console.log(`Aborting...`.red);
    process.exit(1);
}

const allRepos = await client.request("GET /orgs/{org}/repos", {
    org: 'VersaiPE',
    type: 'all',
    per_page: 100
});

allRepos.data = allRepos.data.filter(repo => repo.name !== 'archived');

for (let repo of allRepos.data) {
    if (!repo) {
        console.log(`Could not find repo ${archiveName}.`.red);
        process.exit(1);
    }

    // console.log(`Checking if ${repo.name} (${colors.blue(repo.html_url)}) is stale.`);

    // 6 months
    const staleTime = Date.now() - (1000 * 60 * 60 * 24 * 30 * 6);
    const lastPush = new Date(repo.pushed_at);
    if (lastPush.getTime() < staleTime) {
        const staleDate = new Date(lastPush + (1000 * 60 * 60 * 24 * 30 * 6));
        console.log(`${repo.name.blue} will be stale at: ${staleDate.toLocaleDateString().yellow}`);
        continue;
    }
    console.log(`Archiving ${repo.name.blue}...`)

    const branches = await client.request("GET /repos/{owner}/{repo}/branches", {
        owner: 'VersaiPE',
        repo: repo.name
    });

    console.log(` | ${repo.name.blue} has ${colors.green(branches.data.length)} branches.`);

    // lets create the folder
    fs.mkdirSync(`./repos/${repo.name}`, { recursive: true });

    for (let branch of branches.data) {
        console.log(` | Downloading branch ${branch.name.yellow}...`);
        const archive = await client.request("GET /repos/{owner}/{repo}/zipball/{ref}", {
            owner: 'VersaiPE',
            repo: repo.name,
            ref: branch.name
        });
        // write the archive to the folder
        let encodedName = Buffer.from(branch.name).toString('base64');
        fs.writeFileSync(`./repos/${repo.name}/${encodedName}.zip`, Buffer.from(archive.data), { create: true });
    }

    // now iterate over each zip file and extract it
    for (let branch of branches.data) {
        console.log(` | Extracting branch ${branch.name.yellow}...`);
        // extract the archive
        let encodedName = Buffer.from(branch.name).toString('base64');

        let n = encodeURIComponent(branch.name.replaceAll(' ', '_'));
        await decompress(`./repos/${repo.name}/${encodedName}.zip`, `./repos/${repo.name}/${n}`);

        // write the unzipped archive to the folder

        // delete the zip file
        fs.unlinkSync(`./repos/${repo.name}/${encodedName}.zip`);
    }
    console.log( ` | ${repo.name.blue} archived!`);
}

console.log(`Done.`.green);