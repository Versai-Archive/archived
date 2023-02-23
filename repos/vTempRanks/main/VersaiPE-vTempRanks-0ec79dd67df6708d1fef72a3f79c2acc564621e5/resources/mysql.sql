-- # !mysql
-- # { temp_ranks
-- #   { init
-- #     { table
CREATE TABLE IF NOT EXISTS temp_ranks (
    id INTEGER UNIQUE AUTO_INCREMENT NOT NULL,
    name VARCHAR(16) NOT NULL,
    `rank` VARCHAR(32) NOT NULL,
    until INTEGER
);
-- #     }
-- #   }
-- #   { select
-- #     { player
-- #      :name string
SELECT * FROM temp_ranks WHERE name = :name;
-- #     }
-- #   }
-- #   { insert
-- #     { player.temp_rank
-- #      :name string
-- #      :rank string
-- #      :until int
INSERT INTO temp_ranks(name, `rank`, until)
VALUES (:name, :rank, :until);
-- #     }
-- #   }
-- #   { reset
-- #     { rank
-- #      :name string
-- #      :rank string
DELETE FROM temp_ranks WHERE name=:name AND `rank`=:rank;
-- #     }
-- #   }
-- # }
