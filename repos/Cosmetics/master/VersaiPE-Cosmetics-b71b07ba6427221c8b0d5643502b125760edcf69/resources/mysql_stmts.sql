    -- # !mysql
    -- # { cosmetics
    -- #   { init
    -- #     { table
CREATE TABLE IF NOT EXISTS cosmetics (
    name             VARCHAR(16) UNIQUE NOT NULL,
    xuid             VARCHAR(32) PRIMARY KEY UNIQUE NOT NULL,
    cape             INT UNSIGNED DEFAULT 0,
    spawnFlight      INT UNSIGNED DEFAULT 0,
    hitParticle      INT UNSIGNED DEFAULT 0,
    followParticle   INT UNSIGNED DEFAULT 0,
    tag              INT UNSIGNED DEFAULT 0,
    clanTag          INT UNSIGNED DEFAULT 0,
    customTag        VARCHAR(255) DEFAULT 'None'
);
-- #     }
-- #   }
-- #   { select
-- #     { player
-- #      :name string
SELECT * FROM cosmetics WHERE name = :name;
-- #     }
-- #   }
-- #   { insert
-- #     { player
-- #      :name string
-- #      :xuid string
-- #      :cape int
-- #      :spawnFlight int
-- #      :hitParticle int
-- #      :followParticle int
-- #      :tag int
-- #      :clanTag int
-- #      :customTag string
INSERT INTO cosmetics(name, xuid, cape, spawnFlight, hitParticle, followParticle, tag, clanTag, customTag)
VALUES (:name, :xuid, :cape, :spawnFlight, :hitParticle, :followParticle, :tag, :clanTag, :customTag)
ON DUPLICATE KEY UPDATE name=:name;
-- #     }
-- #   }
-- #   { update
-- #     { player
-- #      :name string
-- #      :cape int
-- #      :spawnFlight int
-- #      :hitParticle int
-- #      :followParticle int
-- #      :tag int
-- #      :clanTag int
-- #      :customTag string
UPDATE cosmetics SET cape=:cape, spawnFlight=:spawnFlight, hitParticle=:hitParticle, followParticle=:followParticle, tag=:tag, clanTag=:clanTag, customTag=:customTag
WHERE name=:name;
-- #     }
-- #   }
-- # }
