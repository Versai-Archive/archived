// Creates a package for the specified archive and zips it.
import colors from 'colors';
import fs from 'fs';
import archiver from 'archiver';
import { client, getArgs } from "./common/client.js";

const name = getArgs()[0];

if (!name) {
    console.log('Please provide a repo name, or names.'.red);
    process.exit(1);
}

if (!fs.existsSync('./.packaged')) {
    fs.mkdirSync('./.packaged');
}

// get the current list of repos
const archivedRepos = fs.readdirSync('./repos');
const archive = archiver('zip', {
    zlib: { level: 9 }
});
const timeStr = new Date().toISOString().replace(/:/g, '-');
const output = fs.createWriteStream(`./.packaged/${timeStr}.zip`);
console.log(`Outputting to ${colors.blue(`.packaged/${timeStr}.zip`)}`);
for (let n of getArgs()) {
    if (!archivedRepos.includes(n)) {
        console.log(`${n} was not found in archives.`.red);
        continue;
    }

    archive.directory(`./repos/${n}`, n);
    console.log(`${'✓'.green} ${colors.yellow(n)} Added to package`);
}

console.log(`Packaging ${name} to .packaged/${timeStr}.zip`.blue);

await new Promise((resolve, reject) => {
    archive
        .on('error', reject)
        .pipe(output);
    output.on('close', resolve);
    archive.finalize();

});

console.log(`\n\n${'✓'.green} Added package! Location: ${colors.blue(`.packaged/${timeStr}.zip`)}`);
