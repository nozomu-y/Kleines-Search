from googleapi_connect import get_credentials
import io
from apiclient.http import MediaIoBaseDownload
import httplib2
from apiclient import discovery
from googleapiclient.http import MediaInMemoryUpload
from config import BASIC_AUTH_USERNAME, BASIC_AUTH_PASSWORD
import requests


def document_to_text(url):
    file_content = download_file(url)

    if file_content is None:
        return None

    MIME_TYPE = 'application/vnd.google-apps.document'

    credentials = get_credentials()
    http = credentials.authorize(httplib2.Http())
    service = discovery.build('drive', 'v3', http=http)

    media_body = MediaInMemoryUpload(file_content, mimetype=MIME_TYPE, resumable=True)

    body = {
        'name': url,
        'mimeType': MIME_TYPE,
    }

    created = service.files().create(
        body=body,
        media_body=media_body,
        ocrLanguage='ja',
    ).execute()

    request = service.files().export_media(fileId=created['id'], mimeType='text/plain')

    fh = io.BytesIO()
    downloader = MediaIoBaseDownload(fh, request)
    done = False
    while done is False:
        status, done = downloader.next_chunk()

    content = fh.getvalue().decode("UTF-8")
    service.files().delete(fileId=created['id']).execute()

    return content


def download_file(url):
    headers = {'Content-Type': 'application/x-www-form-urlencoded'}
    request = requests.post(
        url=url,
        headers=headers,
        auth=(
            BASIC_AUTH_USERNAME,
            BASIC_AUTH_PASSWORD))

    if request.status_code == 200:
        data = request.content
        return data
    else:
        return None


if __name__ == '__main__':
    content = document_to_text('https://www.chorkleines.com/member/kleines_search/sample.docx')
    print(content)
