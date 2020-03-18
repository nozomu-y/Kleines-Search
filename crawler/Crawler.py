from document_to_text import document_to_text
from file_to_text import file_to_text
from html_to_text import html_to_text, html_title
from find_url import find_url
from get_header import get_header
from get_html import get_html
import json
from collections import OrderedDict
import pprint
from mecab import mecab
import requests
import datetime


class Crawler():

    def __init__(self, url):
        self.memory = []
        self.indexed = []
        self.json_file = "index.json"
        self.dump_index(self.memory)
        self.crawler(url, "", 0)

    def crawler(self, url, title, depth):
        self.get_index()
        document = {}
        words = []

        if "chorkleines.com/member/" not in url:
            return
        elif "chorkleines.com/member/bbs/" in url:
            return
        elif "chorkleines.com/member/download/18/pdf_search/" in url:
            return
        elif "chorkleines.com/member/download/18/scoredb/" in url:
            return
        elif "chorkleines.com/member/download/18/past_exam/" in url:
            return
        elif "chorkleines.com/member/wiki/" in url:
            return
        elif "chorkleines.com/member/kleines_search/" in url:
            return

        if url.endswith((".pdf", ".doc", ".docx")):
            if url not in self.indexed:
                text = document_to_text(url)
                if text is None:
                    print("404: " + url)
                    return
                document["url"] = url
                lines = text.splitlines()
                for line in lines:
                    if line != "":
                        line_words = mecab(line)
                        for line_word in line_words:
                            words.append(line_word['text'])
                line_words = mecab(title)
                for line_word in line_words:
                    words.append(line_word['text'])
                document["words"] = words
                document["depth"] = depth
                document["datetime"] = datetime.datetime.now().strftime('%Y/%m/%d %H:%M:%S')
                self.indexed.append(url)
                self.memory.append(document)
                self.dump_index(self.memory)
                print("done: " + url)
            else:
                print("pass: " + url)
            return
        elif url.endswith(("csv", "txt")):
            if url not in self.indexed:
                text = file_to_text(url)
                if text is None:
                    print("404: " + url)
                    return
                document["url"] = url
                lines = text.splitlines()
                for line in lines:
                    if line != "":
                        line_words = mecab(line)
                        for line_word in line_words:
                            words.append(line_word['text'])
                line_words = mecab(title)
                for line_word in line_words:
                    words.append(line_word['text'])
                document["words"] = words
                document["depth"] = depth
                document["datetime"] = datetime.datetime.now().strftime('%Y/%m/%d %H:%M:%S')
                self.indexed.append(url)
                self.memory.append(document)
                self.dump_index(self.memory)
                print("done: " + url)
            else:
                print("pass: " + url)
            return
        elif url.endswith(("mp3", "mp4", "midi", "mid", "wav", "zip", "tar", "gz", "tgz", "jpeg", "jpg", "png", "xlsx", "xls", "pptx", "ppt", " mscz")):
            if url not in self.indexed:
                if get_header(url) is None:
                    print("404: " + url)
                    return
                document["url"] = url
                line_words = mecab(title)
                for line_word in line_words:
                    words.append(line_word['text'])
                document["words"] = words
                document["depth"] = depth
                document["datetime"] = datetime.datetime.now().strftime('%Y/%m/%d %H:%M:%S')
                self.indexed.append(url)
                self.memory.append(document)
                self.dump_index(self.memory)
                print("done: " + url)
            else:
                print("pass: " + url)
            return
        elif url.endswith(("css", "js")):
            return

        data = get_html(url)
        if data is None:
            return
        url = data[0]
        html = data[1]

        if url not in self.indexed:
            text = html_to_text(html)
            title_tmp = html_title(html)
            if title_tmp != "":
                title = title_tmp
            document["url"] = url
            lines = text.splitlines()
            for line in lines:
                if line != "":
                    line_words = mecab(line)
                    for line_word in line_words:
                        words.append(line_word['text'])
            line_words = mecab(title)
            for line_word in line_words:
                words.append(line_word['text'])
            document["words"] = words
            document["depth"] = depth
            document["datetime"] = datetime.datetime.now().strftime('%Y/%m/%d %H:%M:%S')
            self.indexed.append(url)
            self.memory.append(document)
            self.dump_index(self.memory)
            print("done: " + url)
        else:
            print("pass: " + url)

        if depth < 3:
            links = find_url(data)
            for link in links:
                self.crawler(link["href"], link["text"], depth + 1)

        return

    def dump_index(self, document):
        with open(self.json_file, 'w') as f:
            json.dump(document, f, ensure_ascii=False)
            # json.dump(document, f, indent=4, ensure_ascii=False)

    def get_index(self):
        with open(self.json_file) as f:
            self.memory = json.load(f)


Crawler("https://www.chorkleines.com/member/")
