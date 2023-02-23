mysqlHost = "144.217.10.49"
mysqlPort = 3306
mysqlUsername = "adam"
mysqlPassword = "Uyauhi2UTG2cjtwZT2hF%fdf#5TsK"

practiceStatsEndpoint = {
    "req": "post",
    "url": "/v2/player/practice/stats",
    "type": "aplication/json",
    "usage": {"username": "<playername>"}
}

oneblockStatsEndpoint = {
    "req": "post",
    "url": "/v2/player/oneblock/stats",
    "type": "aplication/json",
    "usage": {"username": "<playername>"}
}

voteSiteStatsEndpoint = {
    "req": "post",
    "url": "/v2/player/vote/stats",
    "type": "aplication/json",
    "usage": {"username": "<playername>"}
}