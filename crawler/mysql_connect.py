from sshtunnel import SSHTunnelForwarder
import mysql.connector
import pymysql.cursors
from config import SSH_USERNAME, SSH_PASSWORD, SSH_HOSTNAME, MYSQL_HOSTNAME, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE


def mysql_connect():
    # connect using ssh
    server = SSHTunnelForwarder(
        (SSH_HOSTNAME, 22),
        ssh_host_key=None,
        ssh_username=SSH_USERNAME,
        ssh_password=SSH_PASSWORD,
        ssh_pkey=None,
        remote_bind_address=(MYSQL_HOSTNAME, 3306)
    )
    server.start()

    # connect to mysql
    conn = pymysql.connect(
        host='localhost',
        port=server.local_bind_port,
        user=MYSQL_USERNAME,
        password=MYSQL_PASSWORD,
        database=MYSQL_DATABASE,
        charset='utf8',
    )

    return server, conn
