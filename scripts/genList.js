import colors from 'colors';
import fs from 'fs';

const archivedRepos = fs.readdirSync('./repos');

let fileText = `# Archived Repositories` +
    `\nThis is a list of all archived repositories.` +
    `\n > Do **not** edit this file manually. Use \`pnpm run gen\` to generate this file. ` +
    `\n\n## Archive Index\n\n`;

for (let repo of archivedRepos) {
    fileText += `- [${repo}](/repos/${repo})\n`;
}

fs.writeFileSync('./Index.md', fileText);