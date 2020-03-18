import argparse
from oauth2client import client
from oauth2client import tools
from oauth2client.file import Storage


CLIENT_SECRET_FILE = '../core/client_secret.json'
APPLICATION_NAME = 'Kleines-Search'
SCOPES = 'https://www.googleapis.com/auth/drive.file'
flags = argparse.ArgumentParser(parents=[tools.argparser]).parse_args()


def get_credentials():
    """Gets valid user credentials from storage.

    If nothing has been stored, or if the stored credentials are invalid,
    the OAuth2 flow is completed to obtain the new credentials.

    Returns:
        Credentials, the obtained credential.
    """
    credential_path = './token.json'

    store = Storage(credential_path)
    credentials = store.get()
    if not credentials or credentials.invalid:
        flow = client.flow_from_clientsecrets(CLIENT_SECRET_FILE, SCOPES)
        flow.user_agent = APPLICATION_NAME
        credentials = tools.run_flow(flow, store, flags)
        print('Storing credentials to ' + credential_path)
    return credentials
