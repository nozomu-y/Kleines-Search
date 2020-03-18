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

    print(response.status_code)
    print(url)
    # print(response.text)

# if response.status_code == 200:
#     data = response.text
#     return data
# else:
#     return None


if __name__ == '__main__':
    get_html("https://www.chorkleines.com/member/download/18/calendar")
    get_html("https://www.chorkleines.com/member/download/18/calendar/")
