-- #! mysql
-- #{ vtime
-- #    { init
CREATE TABLE IF NOT EXISTS vtime(
    id       INT         NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    time     INT         NOT NULL DEFAULT '0',
    current  INT,
    PRIMARY KEY (id)
);
-- #    }
-- #    { joined
-- #      :username string
-- #      :current int
INSERT INTO vtime (username, current)
VALUES (:username, :current)
ON DUPLICATE KEY UPDATE current = :current;
-- #    }
-- #    { update
-- #      :username string
-- #      :time int
UPDATE vtime
SET time    = :time - current + time,
    current = 0
WHERE lower(username) = lower(:username);
-- #    }
-- #    { get
-- #      :username string
SELECT *
FROM vtime
WHERE lower(username) = lower(:username);
-- #    }
-- #    { top
SELECT username, time
FROM vtime
ORDER BY time DESC
LIMIT 10;
-- #    }
-- #    { deleteall
DELETE
FROM vtime;
-- #    }
-- #}