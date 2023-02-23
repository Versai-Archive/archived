import requests

def getPlayerVotes(username):

    url = f"https://minecraftpocket-servers.com/api/?object=servers&element=voters&key=4Hzc7MSvLZoy5NjA097zMIGfN26uexLOSr&month=current&format=json"
    response = requests.get(url)
    response = response.json()["voters"]

    for user in response:
        if user["nickname"] == username:
            return user["votes"]
    
    return 0