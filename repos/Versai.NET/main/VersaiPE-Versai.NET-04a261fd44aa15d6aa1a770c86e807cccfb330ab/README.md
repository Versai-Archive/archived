# Versai.NET

### API Endpoints

##### [GET] /api/users/{XUID}

Return player all player data for the given XUID.
```json
{
  "xuid": "123456789",
  "name": "John Doe",
  "class": "Class Name",
  "max_mana": 0,
  "defence": 0,
  "agility": 0,
  "coins": 0,
  "quest": { // add api to get quest ID to all quest data?
    "quest_id": 0,
    "quest_progress": 0
  },
  "levels": {
    "mining": 0,
    "woodcutting": 0,
    "fishing": 0,
    "farming": 0,
    "combat": 0
  },
  // add Kills, Deaths, Keys, etc.. ?
}
```

##### [GET] /api/users/{XUID}/levels

Return all the levels the player has
```json
{
  "mining": 0,
  "woodcutting": 0,
  "fishing": 0,
  "farming": 0,
  "combat": 0
}
```

##### [GET] /api/users/{XUID}/quest

Players quest information

```json
{
  "quest_id": 0,
  "quest_progress": 0
}
```

##### [GET] /api/quests/{QUEST_ID}

```json
{
  "name": "Quest Name",
  "description": "Quest Description",
  "difficulty": "Easy",
  "reward": { // needs to be implemented
    "coins": 0,
    "xp": 0,
    "items": [
      {
        "item_id": 0,
        "quantity": 0
      }
    ]
  }
}
```

Will be adding more as I come up with ideas.
