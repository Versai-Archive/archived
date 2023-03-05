def closeConnection(connection, cursor):
    cursor.close()
    connection.close()
    return None