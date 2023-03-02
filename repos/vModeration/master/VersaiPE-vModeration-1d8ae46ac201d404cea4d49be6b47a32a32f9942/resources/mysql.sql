-- # !mysql
-- # { moderation
-- #   { init
-- #     { players
CREATE TABLE IF NOT EXISTS players (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(16) NOT NULL,
  xuid VARCHAR(32) NOT NULL,
  device_id VARCHAR(64) NOT NULL,
  ip VARCHAR(64) NOT NULL
);
-- #     }
-- #     { bans
CREATE TABLE IF NOT EXISTS bans (
  player_id INTEGER UNIQUE,
  staff_name VARCHAR(16),
  reason VARCHAR(32) NOT NULL,
  until INTEGER,
  FOREIGN KEY (player_id) REFERENCES players(id)
);
-- #     }
-- #     { ip_bans
CREATE TABLE IF NOT EXISTS ip_bans (
  player_id INTEGER UNIQUE,
  staff_name VARCHAR(16),
  reason VARCHAR(32) NOT NULL,
  until INTEGER,
  FOREIGN KEY (player_id) REFERENCES players(id)
);
-- #     }
-- #     { mutes
CREATE TABLE IF NOT EXISTS mutes (
  player_id INTEGER UNIQUE,
  staff_name VARCHAR(16),
  reason VARCHAR(32) NOT NULL,
  until INTEGER,
  FOREIGN KEY (player_id) REFERENCES players(id)
);
-- #     }
-- #     { freezes
CREATE TABLE IF NOT EXISTS freezes (
  player_id INTEGER UNIQUE,
  staff_name VARCHAR(16),
  reason VARCHAR(32) NOT NULL,
  until INTEGER,
  FOREIGN KEY (player_id) REFERENCES players(id)
);
-- #     }
-- #   }
-- #   { insert
-- #     { players
-- #       :player_name string
-- #       :xuid string
-- #       :device_id string
-- #       :ip string
INSERT INTO players (name, xuid, device_id, ip) VALUES (:player_name, :xuid, :device_id, :ip);
-- #     }
-- #   }
-- #   { upsert
-- #     { bans
-- #       :id int
-- #       :staff_name string
-- #       :reason string
-- #       :until int
INSERT INTO bans (player_id, staff_name, reason, until) VALUES (
     :id, :staff_name, :reason, :until
) ON DUPLICATE KEY UPDATE staff_name = :staff_name, reason = :reason, until = :until;
-- #     }
-- #     { ip_bans
-- #       :id int
-- #       :staff_name string
-- #       :reason string
-- #       :until int
INSERT INTO ip_bans (player_id, staff_name, reason, until) VALUES (
     :id, :staff_name, :reason, :until
) ON DUPLICATE KEY UPDATE staff_name = :staff_name, reason = :reason, until = :until;
-- #     }
-- #     { mutes
-- #       :id int
-- #       :staff_name string
-- #       :reason string
-- #       :until int
INSERT INTO mutes (player_id, staff_name, reason, until) VALUES (
    :id, :staff_name, :reason, :until
) ON DUPLICATE KEY UPDATE staff_name = :staff_name, reason = :reason, until = :until;
-- #     }
-- #     { freezes
-- #       :id int
-- #       :staff_name string
-- #       :reason string
-- #       :until int
INSERT INTO freezes (player_id, staff_name, reason, until) VALUES (
     :id, :staff_name, :reason, :until
) ON DUPLICATE KEY UPDATE staff_name = :staff_name, reason = :reason, until = :until;
-- #     }
-- #   }
-- #   { get
-- #     { players
-- #       { all
SELECT * FROM players;
-- #       }
-- #       { player_exclusive
-- #         :player_name string
-- #         :xuid string ~
-- #         :device_id string ~
SELECT * FROM players WHERE LOWER(name) = LOWER(:player_name) AND xuid = :xuid AND device_id = :device_id;
-- #       }
-- #       { player_inclusive
-- #         :player_name string
-- #         :xuid string ~
-- #         :device_id string ~
SELECT * FROM players WHERE LOWER(name) = LOWER(:player_name) OR xuid = :xuid OR device_id = :device_id;
-- #       }
-- #       { player_exclusive_ip
-- #         :player_name string
-- #         :xuid string ~
-- #         :device_id string ~
-- #         :ip string ~
SELECT * FROM players WHERE LOWER(name) = LOWER(:player_name) AND xuid = :xuid AND device_id = :device_id and ip = :ip;
-- #       }
-- #       { player_inclusive_ip
-- #         :player_name string
-- #         :xuid string ~
-- #         :device_id string ~
-- #         :ip string ~
SELECT * FROM players WHERE LOWER(name) = LOWER(:player_name) OR xuid = :xuid OR device_id = :device_id OR ip = :ip;
-- #       }
-- #     }
-- #     { bans
-- #       { all
SELECT bans.*, players.* FROM bans
  INNER JOIN players ON bans.player_id = players.id;
-- #       }
-- #       { player
-- #         :id int
SELECT bans.*, players.name FROM bans
  INNER JOIN players ON bans.player_id = players.id
WHERE players.id = :id;
-- #       }
-- #     }
-- #     { ip_bans
-- #       { all
SELECT ip_bans.*, players.* FROM ip_bans
  RIGHT JOIN players ON ip_bans.player_id = players.id;
-- #       }
-- #       { player
-- #         :id int
SELECT ip_bans.*, players.* FROM ip_bans
  RIGHT JOIN players ON ip_bans.player_id = players.id
WHERE players.id = :id;
-- #       }
-- #     }
-- #     { mutes
-- #       { all
SELECT mutes.*, players.* FROM mutes
  INNER JOIN players ON mutes.player_id = players.id;
-- #       }
-- #       { player
-- #         :id int
SELECT mutes.*, players.name FROM mutes
  INNER JOIN players ON mutes.player_id = players.id
WHERE players.id = :id;
-- #       }
-- #     }
-- #     { freezes
-- #       { all
SELECT freezes.*, players.* FROM freezes
  INNER JOIN players ON freezes.player_id = players.id;
-- #       }
-- #       { player
-- #         :id int
SELECT freezes.*, players.name FROM freezes
  INNER JOIN players ON freezes.player_id = players.id
WHERE players.id = :id;
-- #       }
-- #     }
-- #   }
-- #   { delete
-- #     { bans
-- #       :id int
DELETE FROM bans WHERE player_id = :id;
-- #     }
-- #     { ip_bans
-- #       :id int
DELETE ip_bans FROM ip_bans
  INNER JOIN players ON ip_bans.player_id = players.id
WHERE players.ip = (SELECT ip FROM players WHERE id = :id);
-- #     }
-- #     { mutes
-- #       :id int
DELETE FROM mutes WHERE player_id = :id;
-- #     }
-- #     { freezes
-- #       :id int
DELETE FROM freezes WHERE player_id = :id;
-- #     }
-- #   }
-- # }