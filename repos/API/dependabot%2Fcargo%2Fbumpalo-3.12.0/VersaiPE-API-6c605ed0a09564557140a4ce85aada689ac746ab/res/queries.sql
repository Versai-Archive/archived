SELECT * FROM `player_elo` WHERE id=(SELECT id FROM duel_players WHERE name=:name); (All)
SELECT elo FROM player_elo WHERE id=(SELECT id FROM duel_players WHERE name=:name) AND kit=:kit; (Specific)

SELECT time FROM last_seen WHERE username=:name; (Last Seen)
SELECT time FROM vtime WHERE username=:name; (Online Time)

SELECT `value` FROM streaks WHERE id=(SELECT id FROM player_scores WHERE name=:name);
SELECT `value` FROM kills WHERE id=(SELECT id FROM player_scores WHERE name=:name);
SELECT `value` FROM deaths WHERE id=(SELECT id FROM player_scores WHERE name=:name);
SELECT `value` FROM daily_kills WHERE id=(SELECT id FROM player_scores WHERE name=:name);
SELECT `value` FROM daily_deaths WHERE id=(SELECT id FROM player_scores WHERE name=:name);
SELECT `value` FROM monthly_kills WHERE id=(SELECT id FROM player_scores WHERE name=:name);
SELECT `value` FROM monthly_deaths WHERE id=(SELECT id FROM player_scores WHERE name=:name);
