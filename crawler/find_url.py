from bs4 import BeautifulSoup
from get_html import get_html
import urllib.parse as urlparse


def find_url(data):
    url = data[0]
    html = data[1]
    soup = BeautifulSoup(html, "html.parser")
    links_tmp = soup.find_all("a")

    links = []
    for link in links_tmp:
        link_href = link.get("href")
        link_text = link.text
        if link_href is not None:
            if not (link_href.startswith("#") or link_href.startswith(
                    "mailto:") or link_href.startswith("javascript:")):
                link_href = (urlparse.urljoin(url, link_href))
                link = {"href": link_href, "text": link_text}
                links.append(link)

    return links


if __name__ == "__main__":
    url = "https://www.chorkleines.com/member/"
    data = get_html(url)
    print(find_url(data))
