-- #! mysql
-- #{ vot
-- #    { init
create table if not exists vot (
    id int not null auto_increment,
    username varchar(30) not null,
    time int not null default 0,
    lastSeen int not null default 0,
    primary key (id)
);
-- #    }
-- #    { getTime
-- #      :username string
select time from vot where username = :username;
-- #    }
-- #    { getLastSeen
-- #      :username string
select lastSeen from vot where username = :username;
-- #    }
-- #    { updateTime
-- #      :username string
-- #      :time int
insert into vot (username, time) values (:username, :time) on duplicate key update time = :time;
-- #    }
-- #    { updateLastSeen
-- #      :username string
-- #      :lastSeen int
insert into vot (username, lastSeen) values (:username, :lastSeen) on duplicate key update lastSeen = :lastSeen;
-- #    }
-- #    { getTop
select username, time from vot order by time limit 10;
-- #    }
-- #    { deleteAll
delete from vot;
-- #    }
-- #}