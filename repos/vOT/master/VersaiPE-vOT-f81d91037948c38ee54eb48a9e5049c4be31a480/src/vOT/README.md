# vOT

Versai's Onlinetime + LastSeen Plugin

# Arch

Utilizes MySQL and relies mainly on two rows for the plugin
```sql
time int not null default 0,
lastSeen int not null default 0,
```

`time` - Current timestamp for OT logged

`lastSeen` - Last timestamp that the user logged in

`Loader::$TIMES` - A cache for the log-on time of the player

overall a pretty __poggers__ plugin