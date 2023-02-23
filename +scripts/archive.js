import { client, getArgs } from "./common/client.js";
import colors from 'colors';
import fs from 'fs';
import decompress from 'decompress';

if (getArgs().length === 0) {
    console.log("Please provide a repo name.".red);
    process.exit(1);
}


const allRepos = await client.request("GET /orgs/{org}/repos", {
    org: 'VersaiPE',
    type: 'all',
    per_page: 100
});

const archiveName = getArgs()[0];
const repo = allRepos.data.find(repo => repo.name === archiveName);

if (!repo) {
    console.log(`Could not find repo ${archiveName}.`.red);
    process.exit(1);
}

console.log(`Found repo ${repo.name} (${colors.blue(repo.html_url)})`.green);
console.log(`Archiving repo ${repo.name.blue}...`);
console.log(` | checking branches...`.yellow);

const branches = await client.request("GET /repos/{owner}/{repo}/branches", {
    owner: 'VersaiPE',
    repo: repo.name
});

console.log(` | found ${branches.data.length.green} branches.`);

// lets create the folder
fs.mkdirSync(`./${repo.name}`, { recursive: true });
console.log(`Created folder for archive ${repo.name.blue}`);

for (let branch of branches.data) {
    console.log(`Downloading branch ${branch.name.yellow}...`);
    const archive = await client.request("GET /repos/{owner}/{repo}/zipball/{ref}", {
        owner: 'VersaiPE',
        repo: repo.name,
        ref: branch.name
    });
    // write the archive to the folder
    let encodedName = Buffer.from(branch.name).toString('base64');
    fs.writeFileSync(`./${repo.name}/${encodedName}.zip`, Buffer.from(archive.data), { create: true });
    console.log(` | Done!`.green);
}

// now iterate over each zip file and extract it
console.log(`Extracting...`);
for (let branch of branches.data) {
    console.log(`Extracting branch ${branch.name.yellow}...`);
    // extract the archive
    let encodedName = Buffer.from(branch.name).toString('base64');

    let n = encodeURIComponent(branch.name.replaceAll(' ', '_'));
    await decompress(`./${repo.name}/${encodedName}.zip`, `./${repo.name}/${n}`);

    // write the unzipped archive to the folder

    // delete the zip file
    fs.unlinkSync(`./${repo.name}/${encodedName}.zip`);
    console.log(` | Done!`);
}
console.log(`Archived repo: ${repo.name.blue}!`.green);
console.log(`This repository is now safe to delete.`.green);