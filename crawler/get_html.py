from config import BASIC_AUTH_USERNAME, BASIC_AUTH_PASSWORD
import requests
import urllib.parse as urlparse


def get_html(url):
    headers = {'Content-Type': 'application/x-www-form-urlencoded'}
    response = requests.post(
        url=url,
        headers=headers,
        auth=(
            BASIC_AUTH_USERNAME,
            BASIC_AUTH_PASSWORD))

    history = response.history
    for h in history:
        url = urlparse.urljoin(url, h.headers['Location'])

    if response.status_code == 200:
        data = response.text
        return url, data
    else:
        return None
