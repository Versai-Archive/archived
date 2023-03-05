-- # !mysql
-- # settings {
-- #            { init
-- #              { players
CREATE TABLE IF NOT EXISTS setting_players (
                                             id INTEGER PRIMARY KEY AUTO_INCREMENT,
                                             name VARCHAR(16) NOT NULL
);
-- #              }
-- #              { settings
CREATE TABLE IF NOT EXISTS settings (
  id INTEGER UNIQUE,
  cape INTEGER,
  hit_particles INTEGER,
  follow_particles INTEGER,
  scoreboard BOOLEAN,
  bossbar BOOLEAN,
  flight BOOLEAN,
  tag_1 INTEGER,
  tag_2 INTEGER,
  tag_3 INTEGER,
  custom_tag VARCHAR(16),
  FOREIGN KEY (id) REFERENCES setting_players(id)
);
-- #              }
-- #            }
-- #            { select
-- #              { settings
-- #                :player_name string
SELECT * FROM settings WHERE id = (SELECT id FROM setting_players WHERE name = :player_name);
-- #              }
-- #              { player
-- #                :player_name string
SELECT * FROM setting_players WHERE name = :player_name;
--                }
-- #            }
-- #            { insert
-- #              { player
-- #                :player_name string
INSERT INTO setting_players (name) VALUES (:player_name);
-- #              }
-- #              { settings
-- #                :player_name string
-- #                :cape int
-- #                :hit_particles int
-- #                :follow_particles int
-- #                :scoreboard bool
-- #                :bossbar bool
-- #                :flight bool
-- #                :tag_1 int
-- #                :tag_2 int
-- #                :tag_3 int
-- #                :custom_tag string
REPLACE INTO settings (id, cape, hit_particles, follow_particles, scoreboard, bossbar, flight, tag_1, tag_2, tag_3, custom_tag)
VALUES ((SELECT id FROM setting_players WHERE name = :player_name), :cape, :hit_particles, :follow_particles, :scoreboard, :bossbar, :flight, :tag_1, :tag_2, :tag_3, :custom_tag);
-- #              }
-- #            }
-- #          }