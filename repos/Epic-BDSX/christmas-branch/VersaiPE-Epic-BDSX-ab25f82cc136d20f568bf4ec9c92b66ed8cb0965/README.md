# Versai SMP

This is the complete repository for both the core modules and API for Versai

The core API revolves around simplifying two main aspects, events and commands. These components are wrapped in "modules".

The core plugins are listened below:
- Claims
- Combat Logger
- Main
- TPA
- Ranks (WIP)
- ChatPerks (WIP)
- Esoteric (WIP)

All code contributed should implement the following [Commit Info](/commit-info.md)

# TO-DO
- [x] TPA
- [x] Claims Creation
- [x] Claims Protection
- [ ] Chat Perks
- [ ] PVP **AND** Toolbox/Singleplayer cheats to Esoteric
- [ ] Main Module Logger
- [x] Combat Logger
- [ ] Ranks and Timed Ranks
- [ ] Kits
- [ ] IP-Ban
- [ ] Temp-Mute??
- [ ] Gangs - TLS

# How to use
1. Clone BDSX
2. CD into plugins
3. Clone Versai SMP
4. Run `npm install` -> `tsc index.ts` -> `swc src -d dist --config-file .swcrc`
5. Run BDSX server with start script

When developing, you only need to run `tsc` once (above when initially making plugin), afterwards, you can use the `swc src -d dist --config-file .swcrc` script for faster compilations