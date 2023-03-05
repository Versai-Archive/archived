-- # !sqlite
-- # duels {
-- #         { init
-- #           { players
CREATE TABLE IF NOT EXISTS duel_players (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name VARCHAR(16) NOT NULL
);
-- #           }
-- #           { player_elo
CREATE TABLE IF NOT EXISTS player_elo (
  id INTEGER,
  kit VARCHAR(16),
  elo INTEGER,
  FOREIGN KEY (id) REFERENCES duel_players(id),
  PRIMARY KEY (id, kit)
);
-- #           }
-- #         }
-- #         { select
-- #           { player
-- #             :player_name string
SELECT * FROM duel_players WHERE name = :player_name;
-- #           }
-- #           { id
-- #             :id int
SELECT * FROM duel_players WHERE id = :id;
-- #           }
-- #           { top
-- #             :kit string
-- #             :amount int
SELECT * FROM player_elo WHERE kit = :kit ORDER BY elo DESC LIMIT :amount;
-- #           }
-- #           { elo
-- #             :player_name string
-- #             :kit string
SELECT elo FROM player_elo WHERE id = (SELECT id FROM duel_players WHERE name = :player_name) AND kit = :kit;
-- #           }
-- #           { all_elo
-- #             :player_name string
SELECT * FROM `player_elo` WHERE id = (SELECT id FROM duel_players WHERE name = :player_name);
-- #           }
-- #         }
-- #         { insert
-- #           { player
-- #             :player_name string
INSERT INTO duel_players (name) VALUES (
  :player_name
);
-- #           }
-- #           { elo
-- #             :kit string
-- #             :player_name string
-- #             :elo int
REPLACE INTO player_elo (id, kit, elo) VALUES (
  (SELECT id FROM duel_players WHERE name = :player_name), :kit, :elo
);
-- #           }
-- #         }
