-- #! mysql


-- # { player
-- #    { init
CREATE TABLE IF NOT EXISTS `playerkeys`
(
    `playerID`  int(11) NOT NULL,
    `common`    int(11) NOT NULL DEFAULT 0,
    `rare`      int(11) NOT NULL DEFAULT 0,
    `epic`      int(11) NOT NULL DEFAULT 0,
    `legendary` int(11) NOT NULL DEFAULT 0
);
-- #    }
-- #    { select
-- #        :uuid string
SELECT id
FROM skyblock.players
WHERE uuid = :uuid;
-- #    }
-- #  }
-- # { keys
-- #    { select
-- #        :id int
SELECT *
FROM skyblock.playerkeys
WHERE playerID = :id;
-- #    }
-- #    { insert
-- #        :id int
INSERT INTO skyblock.playerkeys (playerID)
VALUES (:id);
-- #    }
-- #    { delete
-- #        :id int
DELETE
FROM skyblock.playerkeys
WHERE playerID = :id;
-- #    }
-- # }