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

for (let repo of allRepos.data) {
    if (!repo) {
        console.log(`Could not find repo ${archiveName}.`.red);
        process.exit(1);
    }

    console.log(`Archiving repo ${repo.name} (${colors.blue(repo.html_url)})`.green);
    console.log(` | checking branches...`.yellow);

    const branches = await client.request("GET /repos/{owner}/{repo}/branches", {
        owner: 'VersaiPE',
        repo: repo.name
    });

    console.log(` | found ${branches.data.length.green} branches.`);

    // lets create the folder
    fs.mkdirSync(`./repos/${repo.name}`, { recursive: true });
    console.log(`Created folder for archive ${repo.name.blue}`);

    for (let branch of branches.data) {
        console.log(`| Downloading branch ${branch.name.yellow}...`);
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
        console.log(`| Extracting branch ${branch.name.yellow}...`);
        // extract the archive
        let encodedName = Buffer.from(branch.name).toString('base64');

        let n = encodeURIComponent(branch.name.replaceAll(' ', '_'));
        await decompress(`./repos/${repo.name}/${encodedName}.zip`, `./repos/${repo.name}/${n}`);

        // write the unzipped archive to the folder

        // delete the zip file
        fs.unlinkSync(`./repos/${repo.name}/${encodedName}.zip`);
    }
    console.log(`Archived repo: ${repo.name.blue}!`.green);
}