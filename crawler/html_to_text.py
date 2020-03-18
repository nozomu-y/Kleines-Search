from config import BASIC_AUTH_USERNAME, BASIC_AUTH_PASSWORD
import requests
from html.parser import HTMLParser
import io
import re


class MLStripper(HTMLParser):
    def __init__(self, s):
        super().__init__()
        self.sio = io.StringIO()
        self.feed(s)

    def handle_starttag(self, tag, attrs):
        pass

    def handle_endtag(self, tag):
        pass

    def handle_data(self, data):
        self.sio.write(data)

    @property
    def value(self):
        return self.sio.getvalue()


def html_to_text(html):
    return MLStripper(html).value


def html_title(html):
    pattern = re.compile("<title>(.+?)</title>")
    titles = re.findall(pattern, html)
    if len(titles) > 0:
        return titles[0]
    else:
        return ""
