# vOneBlock

This branch of the OneBlock repository is made to add island members, so that you can co-op with players and have more interactions in game!

## TODO

- [ ] Permissions
    - [ ] Rank Based
    - [ ] Custom
        - [ ] Player go to island even when owner offline
    - [ ] Banned
- [ ] Island Visitation
    - [ ] Island Banning
    - [ ] Island Locking
- [ ] Display the stats of the island that the player is on
    - [ ] BossBar
    - [ ] ScoreBoard

### Brain Dump

```txt
The players will be able to add a player to there island with either a custom set of permissions or a base rank for island members. They will be able to teleport to the island even when the player is offline, This means that we will have to make a system to handle all of this information and checks

The island manager should check for wether or not the island is loaded, and the island should only be loaded if the member goes to teleport to the island, or if the owner is online
```