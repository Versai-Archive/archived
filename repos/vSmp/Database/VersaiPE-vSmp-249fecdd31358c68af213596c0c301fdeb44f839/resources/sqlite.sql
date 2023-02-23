-- #! sqlite
-- #{ rpgcore
-- # { init
-- #  { table1
CREATE TABLE IF NOT EXISTS Players(
    ID INTEGER PRIMARY KEY AUTOINCREMENT,
    XUID VARCHAR(21),
    Username VARCHAR(21)
);
-- #  }
-- #  { table2
CREATE TABLE IF NOT EXISTS PlayerData(
    ID INT UNIQUE,
    Class VARCHAR(16),
    MaxMana INT DEFAULT 1,
    Defense INT DEFAULT 0,
    Agility FLOAT DEFAULT 0.10,
    Coins INT DEFAULT 1000,
    QuestId INT DEFAULT 0,
    QuestProgress INT DEFAULT 0,
    FOREIGN KEY (ID) REFERENCES Players(ID)
);
-- #  }
-- # }
-- # { player
-- #  { startplayerdata
INSERT OR REPLACE INTO PlayerData WHERE ID=(SELECT ID FROM Players WHERE Username=:username) (
    Class,
    MaxMana,
    Defense,
    Agility,
    Coins,
    QuestId,
    QuestProgress
) VALUES (
    :class,
    :MaxMana,
    :defense,
    :agility,
    :coins,
    :questid,
    :questprogress
);
-- #  }
-- #  { startplayers
INSERT OR REPLACE INTO Players(
    XUID,
    Username
) VALUES (
    :xuid,
    :usernames
);
-- #  }
-- #  { getData
SELECT * FROM PlayerData WHERE ID=(SELECT ID FROM Players WHERE Username=:username);
-- #  }
-- #  { getClass
SELECT Class FROM PlayerData WHERE ID=(SELECT ID FROM Players WHERE Username=:username);
-- #  }
-- #  { getMaxMana
SELECT MaxMana FROM PlayerData WHERE ID=(SELECT ID FROM Players WHERE Username=:username);
-- #  }
-- #  { getDefense
SELECT Defense FROM PlayerData WHERE ID=(SELECT ID FROM Players WHERE Username=:username);
-- #  }
-- #  { getAgility
SELECT Agility FROM PlayerData WHERE ID=(SELECT ID FROM Players WHERE Username=:username);
-- #  }
-- #  { getCoins
SELECT Coins FROM PlayerData WHERE ID=(SELECT ID FROM Players WHERE Username=:username);
-- #  }
-- #  { getQuestId
SELECT QuestId FROM PlayerData WHERE ID=(SELECT ID FROM Players WHERE Username=:username);
-- #  }
-- # }
-- #}