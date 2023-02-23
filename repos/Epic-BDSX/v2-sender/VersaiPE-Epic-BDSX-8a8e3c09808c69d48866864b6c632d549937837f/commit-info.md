# Basic requirements of codebase
- Typescript
- Modern ES6 Syntax associated with TS
- Require little third party modules (unless absolutely required and cannot be rewritten)
- Use 4 spaces for indents
- Must use `camelCase` conventions for variables, `CAPITAL_SNAKE_CASE` conventions for constants, and `PascalCase` for class, interface, and other object data structure declarations

# Requirements for commits/pushes.
- All code must be free of compile time errors
- All code must be free of runtime errors
- Code should not use third party libraries unless absolutely necessary
- All code should follow the commit styling
- All code array-based code should use array methods like `Array.forEach`, `Array.map`, or `Array.reduce`, limit explicit iterations over an array (ALWAYS use `for (let item of arr)` instead of `Array.forEach`)
- All code MUST use modern ES6 or JS syntax, e.g
> `const main = () => {}` instead of `function main() => {}` (also applies to callbacks) 
> `async/await` instead of `new Promise((resolve, reject) => {})`

# Message style for commits.
All commits should have the following style: `<emoji>(file?): Description`. An example:
> 🎉 feature: Voice channel support! <br />
> 📝 chore(test.ts): Fix a few types

### Chores
**Name:** chore <br/>
**Prefix:** 📝<br/>
**Descrption:** A chore can include, a minor fix, and audit, or something that involves repitition.

### Features
**Name:** feature<br/>
**Prefix:** 🎉<br/>
**Description:** This commit implements a new feature.

### Release
**Name:** release<br/>
**Prefix:** 🚀<br/>
**Description:** This commit releases a new version of the project.

### Deprecation
**Name:** deprecate<br/>
**Prefix:** 😒<br/>
**Description:** This commit audits a method or property that is now deprecated and may be removed in the future.

### Tests
**Name:** test<br/>
**Prefix:** 🧪<br/>
**Description:** A test can include a piece of code that performs a functional check on the project.

### Create
**Name:** create<br/>
**Prefix:** 🆕<br/>
**Description:** This commit creates a file or (related). **SHOULD NOT** be used for feature implementation.

### Removal or Deletion
**Name:** delete<br/>
**Prefix:** 🗑<br/>
**Description:** This commit removes one or multiple files.

### Bulk
**Name:** bulk<br/>
**Prefix:** 🛒<br/>
**Description:** Includes a bulk amount of changes.

### Merge
**Name:** merge<br/>
**Prefix:** 📩<br/>
**Description:** This commit merges a branch into another (or related).