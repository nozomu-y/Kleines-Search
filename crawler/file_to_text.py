from config import BASIC_AUTH_USERNAME, BASIC_AUTH_PASSWORD
import requests


def file_to_text(url):
    headers = {'Content-Type': 'application/x-www-form-urlencoded'}
    request = requests.post(
        url=url,
        headers=headers,
        auth=(
            BASIC_AUTH_USERNAME,
            BASIC_AUTH_PASSWORD))

    if request.status_code == 200:
        request.encoding = request.apparent_encoding
        data = request.text
        return data
    else:
        return None
