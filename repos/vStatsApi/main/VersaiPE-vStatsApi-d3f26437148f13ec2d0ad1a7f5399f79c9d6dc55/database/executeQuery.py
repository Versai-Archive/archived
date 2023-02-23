from database.closeConnection import closeConnection
import storage.config as config

def executeQuery(connection, query):
    cur = connection.cursor()
    cur.execute(query)
    result = cur.fetchall()
    closeConnection(connection, cur)
    return result