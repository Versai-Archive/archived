-- #!sqlite
-- #{ skyblock
-- #    { init
-- #        { players
CREATE TABLE IF NOT EXISTS "players"
(
    "id"             INTEGER UNIQUE,
    "username"       TEXT,
    "job"            INTEGER DEFAULT 0,
    "rank"           INTEGER DEFAULT 0,
    "job_changed_at" INTEGER DEFAULT 0,
    "last_join"      INTEGER DEFAULT 0,
    "first_join"     INTEGER DEFAULT 0,
    PRIMARY KEY ("id" AUTOINCREMENT)
);
-- #        }
-- #        { islands
CREATE TABLE IF NOT EXISTS "islands"
(
    "id"                  INTEGER UNIQUE,
    "owner_id"            INTEGER,
    "spawn_position"      VARCHAR(64),
    "last_owner_activity" INT DEFAULT NULL,
    PRIMARY KEY ("id" AUTOINCREMENT),
    FOREIGN KEY ("owner_id") REFERENCES players ("id")
);
-- #        }
-- #    }
-- #    { get
-- #        { player
-- #            { by_name
-- #             :name string
SELECT *
FROM players
WHERE lower("username") = lower(:name);
-- #            }
-- #            { by_id
-- #             :id int
SELECT *
FROM players
WHERE "id" = :id;
-- #            }
-- #            { all
SELECT *
FROM players;
-- #            }
-- #        }
-- #        { island
-- #            { all
SELECT *
FROM islands;
-- #            }
-- #        }
-- #    }
-- #    { create
-- #        { player
-- #         :username string
-- #         :created_at int
INSERT INTO players (
                     username,
                     first_join,
                     last_join)
            VALUES (
                    :username,
                    :created_at,
                    :created_at
);
-- #        }
-- #    }
-- #} 