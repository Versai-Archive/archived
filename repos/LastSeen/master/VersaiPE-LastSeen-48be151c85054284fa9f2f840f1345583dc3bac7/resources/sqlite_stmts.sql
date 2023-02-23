-- # !sqlite

-- #{lastseen

-- #  {init
CREATE TABLE IF NOT EXISTS Players
(
    username VARCHAR(36),
    time VARCHAR(64)
);
-- #  }

-- #  {time
-- #    {get
-- #      :username string
SELECT time
FROM Players
WHERE username = :username;
-- #    }
-- #    {set
-- #      :time     string
-- #      :username string
UPDATE Players
SET time = :time
WHERE username = :username COLLATE NOCASE;
-- #    }
-- #    {register
-- #      :username string
-- #      :time     string
INSERT INTO Players(username, time)
VALUES(:username, :time);
-- #    }
-- #  }

-- #}