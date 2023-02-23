import pymysql
import storage.config as config

def createConnection(schema):
    cnx = pymysql.connect( 
        user=f"{config.mysqlUsername}", 
        password=f"{config.mysqlPassword}",                 
        host=f"{config.mysqlHost}",
        database=f"{schema}"
    )

    return cnx