import os

path = os.getcwd()
ignore = [
  '.cache', '.config', '.upm', 'poetry.lock', '.breakpoints', 'replit.nix',
  'venv', 'pyproject.toml', '.replit', 'counter.py', 'LICENSE',
  'phpstan.neon.dist', 'composer.json', 'composer.lock', '.php-cs-fixer.php',
  '.gitmodules', '.gitattributes', '.gitignore', '.editorconfig', 'vendor',
  '.git'
]
print("Counting directory: ", path)

def parseDirectory(dir):
  global lines
  global files
  global dirs
  for file in os.listdir(dir):
    if (file in ignore):
      continue
    if (os.path.isfile(dir + "/" + file)):
      files += 1
      parseFile(dir + "/" + file)
      continue
    if (os.path.isdir(dir + "/" + file)):
      dirs += 1
      parseDirectory(dir + "/" + file)

def parseFile(file):
  global lines
  with open(file, 'rb') as f:
    contents = f.readlines()
    lns = len(contents)
    lines += lns

files = 0
dirs = 0
lines = 0

parseDirectory(path)

print(f"""
Total Directorys: {dirs}
Total Files: {files}
Total Lines: {lines}
""")

#print("Total Directorys: ", dirs)
#print("Total Files: ", files)
#print("Total Lines: ", lines)
