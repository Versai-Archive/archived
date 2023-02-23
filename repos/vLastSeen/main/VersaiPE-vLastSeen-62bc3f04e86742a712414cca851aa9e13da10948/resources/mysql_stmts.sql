-- # !mysql
-- #{lastseen
-- #  {init
CREATE TABLE IF NOT EXISTS last_seen(
    id INTEGER AUTO_INCREMENT UNIQUE,
    username VARCHAR(36) PRIMARY KEY,
    time VARCHAR(64)
);
-- #  }
-- #  {time
-- #    {get
-- #      :username string
SELECT time FROM last_seen WHERE username=:username;
-- #    }
-- #    {set
-- #      :time     string
-- #      :username string
UPDATE last_seen SET time=:time WHERE username=:username;
-- #    }
-- #    {update
-- #      :username string
-- #      :time     string
INSERT INTO last_seen(username, time)
VALUES(:username, :time)
ON DUPLICATE KEY UPDATE
    time=:time
-- #    }
-- #  }
-- #}