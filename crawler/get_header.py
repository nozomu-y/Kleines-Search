from config import BASIC_AUTH_USERNAME, BASIC_AUTH_PASSWORD
import requests


def get_header(url):
    headers = {'Content-Type': 'application/x-www-form-urlencoded'}
    request = requests.head(
        url=url,
        headers=headers,
        auth=(
            BASIC_AUTH_USERNAME,
            BASIC_AUTH_PASSWORD))

    if request.status_code == 200:
        return 200
    else:
        return None
