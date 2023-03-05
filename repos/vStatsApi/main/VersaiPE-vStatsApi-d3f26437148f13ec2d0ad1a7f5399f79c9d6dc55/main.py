#pyright: reportMissingModuleSource=false
#pyright: reportMissingImports=false

# defined imports
import os
import requests
from fastapi import FastAPI, Request

# user defined imports
import storage.config as config
from functions.getKDR import getKDR
from functions.getPlayerVotes import getPlayerVotes
from database.createConnection import createConnection
from database.executeQuery import executeQuery

# main constants
app = FastAPI(docs_url=None, redoc_url=None)

# endpoint management
@app.post(config.practiceStatsEndpoint["url"])
async def reqPracticeUserStats(info: Request):

    data = await info.json()
    username = data["username"]

    connection = createConnection("practice")
    results = executeQuery(connection, "SELECT * FROM practice_players;")
    for user in results:
        if user[1] == username:
            data = {
                "id": user[0], 
                "username": user[1], 
                "xuid": user[2], 
                "kills": user[3], 
                "deaths": user[4], 
                "kdr": getKDR(user[3], user[4]),
                "daily_kills": user[5],
                "deaily_deaths": user[6],
                "daily_kdr": getKDR(user[5], user[6]),
                "monthly_kills": user[7],
                "monthly_deaths": user[8],
                "monthly_kdr": getKDR(user[7], user[8]),
                "killstreak": user[9]
            }
            response = {"status": "Success", "data": data}
            return response

    status = {"status": "Failed", "Error": "User Doesnt Exist!"}
    return status

@app.post(config.oneblockStatsEndpoint["url"])
async def reqOneblockUserStats(info: Request):

    data = await info.json()
    username = data["username"]

    connection = createConnection("practice")
    results = executeQuery(connection, "SELECT * FROM player_data;")
    for user in results:
        if user[1] == username:
            data = {
                "xuid": user[0], 
                "username": user[1], 
                "coins": user[2], 
                "kills": user[3], 
                "deaths": user[4], 
                "blocks_broken": user[5],
                "blocks_placed": user[6]
            }
            response = {"status": "Success", "data": data}
            return response

    status = {"status": "Failed", "Error": "User Doesnt Exist!"}
    return status

@app.post(config.voteSiteStatsEndpoint["url"])
async def reqVoteSiteStats(info: Request):

    data = await info.json()
    username = data["username"]
    votes = getPlayerVotes(username)
    data = {
        "username": username,
        "votes": int(votes)
    }
    response = {"status": "Success", "data": data}
    return response