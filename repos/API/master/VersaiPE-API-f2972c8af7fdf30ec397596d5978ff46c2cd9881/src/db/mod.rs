use mysql::prelude::*;
use mysql::{Error, Pool, PooledConn};

pub mod structs;
use structs::PlayerStats;

pub struct Database {
    pub url: String,
    pub conn: PooledConn,
    // pub cache: HashMap<idk, impl Cacheable>,
}
impl Database {
    pub fn new(url: String) -> Self {
        let pool = Pool::new(&url).unwrap();
        let conn = pool.get_conn().unwrap();
        Self { url, conn }
    }

    /// Query all player stats
    pub fn query_all_player_stats(&mut self) -> Result<Vec<PlayerStats>, Error> {
        self.conn.query_map(
            "select * from players",
            |(
                ign,
                kills,
                deaths,
                kdr,
                daily_kills,
                daily_deaths,
                daily_kdr,
                monthly_kills,
                monthly_deaths,
                monthly_kdr,
                ks,
            )| {
                PlayerStats {
                    ign,
                    kills,
                    deaths,
                    kdr,
                    daily_kills,
                    daily_deaths,
                    daily_kdr,
                    monthly_kills,
                    monthly_deaths,
                    monthly_kdr,
                    ks,
                }
            },
        )
    }

    /// Query a player by their IGN
    pub fn query_player_stats(&mut self, ign: &str) -> Result<Vec<PlayerStats>, Error> {
        self.conn.query_map(
            format!("select * from players where ign = {}", ign),
            |(
                ign,
                kills,
                deaths,
                kdr,
                daily_kills,
                daily_deaths,
                daily_kdr,
                monthly_kills,
                monthly_deaths,
                monthly_kdr,
                ks,
            )| {
                PlayerStats {
                    ign,
                    kills,
                    deaths,
                    kdr,
                    daily_kills,
                    daily_deaths,
                    daily_kdr,
                    monthly_kills,
                    monthly_deaths,
                    monthly_kdr,
                    ks,
                }
            },
        )
    }
}
