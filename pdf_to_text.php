<?php
/*
 * Copyright (c) 2020 Nozomu Yamazaki
 * Released under the MIT license
 * https://opensource.org/licenses/mit-license.php
 * 
 * extract text from pdf
 */
require_once __DIR__ . '/core/googleapi.php';
require_once __DIR__ . '/vendor/autoload.php';

/**
 * extract text from pdf
 * @param string $pdf
 * @return string
 */
function pdf_to_text(string $url)
{
    try {
        $client = getClient();
        $service = new Google_Service_Drive($client);

        // get pdf file
        require(__DIR__ . "/core/config.php");

        $data = http_build_query($_POST, "", "&");
        $header = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . base64_encode($basic_auth_password)
        );
        $options = array(
            'http' => array(
                'method' => 'POST',
                'content' => $data,
                'header' => implode("\r\n", $header),
            )
        );
        $options = stream_context_create($options);
        $content = @file_get_contents($url, false, $options);

        // configuration of the file to upload
        $meta = new Google_Service_Drive_DriveFile(array(
            // upload as google document
            'mimeType' => 'application/vnd.google-apps.document'
        ));

        // upload file
        $created = $service->files->create($meta, array(
            'data' => $content,
            'mimeType' => 'application/pdf',
            'uploadType' => 'multipart',
            'fields' => 'id'
        ));

        // download the file as text
        $response = $service->files->export($created->id, 'text/plain', array(
            'alt' => 'media'
        ));

        if ($response->getStatusCode() == 200) {
            $content = $response->getBody()->getContents();
            // delete the uploaded file
            $service->files->delete($created->id);
            return $content;
        }
    } catch (Google_Service_Exception $e) {
        echo '<strong>' . $e->getMessage() . '</strong>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    } catch (Google_Exception $e) {
        echo '<strong>' . $e->getMessage() . '</strong>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    } catch (Exception $e) {
        echo '<strong>' . $e->getMessage() . '</strong>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    }
}
