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
from mysql_connect import mysql_connect
import os


class Crawler():

    def __init__(self, url):
        self.memory = []
        self.crawler(url, "", 0)

    def __del__(self):
        self.conn.close()
        self.server.stop()

    def crawler(self, url, title, depth):
        target = datetime.datetime.now() - datetime.timedelta(days=30)
        target = target.strftime('%Y/%m/%d %H:%M:%S')
        query = "SELECT * FROM documents WHERE last_index IS NOT NULL AND last_index > %s"
        res = mysql_connect()
        self.server = res[0]
        self.conn = res[1]
        cur = self.conn.cursor()
        cur.execute(query, (target,))
        cur.close()
        for row in cur:
            self.memory.append(row[1])

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
            if url not in self.memory:
                text = document_to_text(url)
                if text is None:
                    print("404: " + url)
                    return
                doc_id = self.insert_document(url, title)
                lines = text.splitlines()
                for line in lines:
                    if line != "":
                        line_words = mecab(line)
                        for line_word in line_words:
                            self.insert_word(line_word['text'], doc_id)
                line_words = mecab(title)
                for line_word in line_words:
                    self.insert_word(line_word['text'], doc_id)
                self.insert_done(doc_id)
                print("done: " + url)
            else:
                print("pass: " + url)
            return
        elif url.endswith((".csv", ".txt")):
            if url not in self.memory:
                text = file_to_text(url)
                if text is None:
                    print("404: " + url)
                    return
                doc_id = self.insert_document(url, title)
                lines = text.splitlines()
                for line in lines:
                    if line != "":
                        line_words = mecab(line)
                        for line_word in line_words:
                            self.insert_word(line_word['text'], doc_id)
                line_words = mecab(title)
                for line_word in line_words:
                    self.insert_word(line_word['text'], doc_id)
                self.insert_done(doc_id)
                print("done: " + url)
            else:
                print("pass: " + url)
            return
        elif url.endswith((".mp3", ".mp4", ".midi", ".mid", ".wav", ".zip", ".tar", ".gz", ".tgz", ".jpeg", ".jpg", ".png", ".xlsx", ".xls", ".pptx", ".ppt", ".mscz")):
            if url not in self.memory:
                if get_header(url) is None:
                    print("404: " + url)
                    return
                doc_id = self.insert_document(url, title)
                line_words = mecab(title)
                for line_word in line_words:
                    self.insert_word(line_word['text'], doc_id)
                self.insert_done(doc_id)
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

        if url not in self.memory:
            text = html_to_text(html)
            title_tmp = html_title(html)
            if title_tmp != "":
                title = title_tmp
            doc_id = self.insert_document(url, title)
            lines = text.splitlines()
            for line in lines:
                if line != "":
                    line_words = mecab(line)
                    for line_word in line_words:
                        self.insert_word(line_word['text'], doc_id)
            line_words = mecab(title)
            for line_word in line_words:
                self.insert_word(line_word['text'], doc_id)
            self.insert_done(doc_id)
            print("done: " + url)
        else:
            print("pass: " + url)
            # return

        if depth < 3:
            links = find_url(data)
            for link in links:
                self.crawler(link["href"], link["text"], depth + 1)

        return

    def insert_document(self, url, title):
        if url.endswith(
            ("mp3",
             "mp4",
             "midi",
             "mid",
             "wav",
             "zip",
             "tar",
             "gz",
             "tgz",
             "jpeg",
             "jpg",
             "png",
             "xlsx",
             "xls",
             "pptx",
             "ppt",
             " mscz",
             ".pdf",
             ".doc",
             ".docx",
             "csv",
             "txt")):
            filetype = os.path.splitext(url)[1][1:]
        else:
            filetype = ''

        query = """INSERT INTO documents (url, title, filetype)
            SELECT * FROM (SELECT %s AS url, %s AS title, %s AS filetype) AS tmp
            WHERE NOT EXISTS (
                SELECT url FROM documents WHERE url = %s
            ) LIMIT 1
            """
        cur = self.conn.cursor()
        url = self.conn.escape(url)
        title = self.conn.escape(title)
        filetype = self.conn.escape(filetype)
        cur.execute(query, (url, title, filetype, url))
        self.conn.commit()
        cur.close()

        query = "SELECT id FROM documents WHERE url = %s"
        cur = self.conn.cursor()
        url = self.conn.escape(url)
        cur.execute(query, (url,))
        cur.close()

        for row in cur:
            doc_id = row[0]
        doc_id = str(doc_id)
        doc_id = doc_id.zfill(10)

        query = "SHOW COLUMNS FROM inverted_index"
        cur = self.conn.cursor()
        cur.execute(query)
        cur.close()

        columns = []
        for row in cur:
            columns.append(row[0])

        if doc_id not in columns:
            query = "ALTER TABLE inverted_index ADD COLUMN `%s` int(5) DEFAULT '0'"
            cur = self.conn.cursor()
            doc_id = self.conn.escape(doc_id)
            cur.execute(query % (doc_id,))
            self.conn.commit()
            cur.close()

        query = "UPDATE inverted_index SET `%s` = 0"
        cur = self.conn.cursor()
        doc_id = self.conn.escape(doc_id)
        cur.execute(query % (doc_id,))
        self.conn.commit()
        cur.close()

        return doc_id

    def insert_word(self, text, doc_id):
        query = """INSERT INTO inverted_index(word)
            SELECT * FROM(SELECT %s) AS tmp
            WHERE NOT EXISTS(
                SELECT word FROM inverted_index WHERE word=%s
            ) LIMIT 1"""
        cur = self.conn.cursor()
        text = self.conn.escape(text)
        cur.execute(query, (text, text))
        self.conn.commit()
        cur.close()

        query = "UPDATE inverted_index SET `%s` = `%s` + 1 WHERE word =%s"
        cur = self.conn.cursor()
        doc_id = self.conn.escape(doc_id)
        text = self.conn.escape(text)
        cur.execute(query % (doc_id, doc_id, text))
        self.conn.commit()
        cur.close()

        return

    def insert_done(self, doc_id):
        query = "UPDATE documents SET last_index = NOW() WHERE id=%s"
        cur = self.conn.cursor()
        doc_id = self.conn.escape(doc_id)
        cur.execute(query, (doc_id,))
        self.conn.commit()
        cur.close()

        return


Crawler("https://www.chorkleines.com/member/download/18/")
