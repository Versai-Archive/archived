-- #!sqlite
-- #{ groupsapi
-- # { init
CREATE TABLE IF NOT EXISTS users (name VARCHAR(20) NOT NULL PRIMARY KEY, `groups` TEXT NOT NULL)
-- # }

-- # { default_group_table
CREATE TABLE IF NOT EXISTS `groups` (name VARCHAR(15) NOT NULL PRIMARY KEY, permissions TEXT NOT NULL, priority INT NOT NULL, inherits TEXT NOT NULL)
-- # }

-- # { create_group
-- #   :name string
-- #   :permission string
-- #   :priority int
-- #   :inherits string
INSERT INTO `groups` (name, permissions, priority, inherits) VALUES (:name, :permission, :priority, :inherits)
-- # }

-- # { create_user
-- #   :name string
-- #   :group_list string
INSERT INTO users (name, `groups`) VALUES (:name, :group_list)
-- # }

-- # { get_groups
SELECT * FROM `groups`
-- # }

-- # { get_group
-- #   :name string
SELECT * FROM `groups` WHERE name = :name
-- # }

-- # { get_user
-- #   :name string
SELECT * FROM users WHERE name = :name
-- # }

-- # { update_user
-- #   :name string
-- #   :group_list string
UPDATE users SET `groups` = :group_list WHERE name = :name
-- # }

-- # { delete_group
-- #   :name string
DELETE FROM `groups` WHERE name = :name
-- # }

-- # { update_group
-- #   :name string
-- #   :permission string
-- #   :priority int
-- #   :inherits string
UPDATE `groups` SET permissions = :permission, priority = :priority, inherits = :inherits WHERE name = :name
-- # }
-- #}