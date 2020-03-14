# Kleines Search

Search Engine developed for Chor Kleines website's Member's Area configured with Basic Authentication.

Kleines Search enables you to search whatever information inside the Member's Area.

**NOTE** This software is now under development and not yet deployed for practical use.

## Requirements

- PHP 7.3.14
- MySQL 5.7
- mecab 0.996

## Libraries

### Google API Client

https://github.com/googleapis/google-api-php-client  
Used in order to extract text from PDF files via Google Drive and Google Docs

### youaoi/php-mecab

https://github.com/youaoi/php-mecab  
Used in order to parse text into words using morphological analysis  
Requires [mecab](https://taku910.github.io/mecab/) (EUC-JP)

## License

Copyright &copy; 2020 Nozomu Yamazaki  
Released under the MIT license  
https://opensource.org/licenses/mit-license.php
