-- #! mysql


-- # { player
-- #   { init
CREATE TABLE `players` (
  `id` int(11) NOT NULL,
  `uuid` varchar(100) NOT NULL,
  `name` varchar(20) NOT NULL
);
-- #  }
-- #   { insert
-- #        :uuid string
-- #        :name string
INSERT INTO skyblock.players (uuid, name) VALUE (:uuid, :name);
-- #    }
-- #    { select
-- #        :uuid string
SELECT id
FROM skyblock.players
WHERE uuid = :uuid;
-- #    }
-- #
-- # }