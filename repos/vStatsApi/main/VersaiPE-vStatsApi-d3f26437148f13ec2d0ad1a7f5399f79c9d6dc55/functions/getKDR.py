def getKDR(kills, deaths):
    if int(deaths) == 0: return int(kills)
    if int(kills) == 0: return 0
    
    try:
        kdr = int(kills) / int(deaths)
        return round(kdr)
    except:
        return 0