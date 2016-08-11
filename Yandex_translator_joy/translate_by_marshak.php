<?php
/*
Plugin Name: Translate content by Marshak I.
Plugin URI: http://
Description: Описание плагина
Version: 1.0
Author: Marshak Igor
Author URI: http://
License: Лицензия
*/

function getTranslatedTextOfMarshak($text) {

    // For not power on admin panel.
    if (is_admin()) {
        return $text;
    }

    // URL Yandex translate
    define('YANDEX_TRANSLATE_API_URI', 'https://translate.yandex.net/api/v1.5/tr.json/translate');

    // Yandex signature. The requirement of the user agreement.
    define('YANDEX_SIGNATURE', "<br/><a href='http://translate.yandex.com/'> \"Powered by Yandex.Translate\"</a>");

    // Yandex API text format, may be  ( plain || html )
    define('YANDEX_FORMAT', 'html');

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // Yandex API key. There must be a real key, issued by Yandex. As string.
    $yandexKey = '';
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


    // Text may be have not valid characters for GET request.
    $urlencodeText = urlencode($text);


    // Validation client language
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $langTarget = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    } else {
        return separatorTitleAndPost($text, '<b style = "color:red"><i>Warning!!! Can\'t translate text because not defined client language.</i></b>');
    }


    // Create URL for request
    $urlReq = YANDEX_TRANSLATE_API_URI
            . "?"
            . "key={$yandexKey}&"             // API Key
            . "text={$urlencodeText}&"        // Source text for translate
            . "lang={$langTarget}&"           // Direction of translation << ru || en-ru>>
            . "format=" . YANDEX_FORMAT;


    // Request to Yandex
    if ($curl = curl_init()) {
        curl_setopt($curl, CURLOPT_URL, $urlReq);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($curl, CURLOPT_HEADER, true);
        $responseJSON = curl_exec($curl);
        curl_close($curl);
    } else {
        return separatorTitleAndPost($text, '<b style = "color:red"><i>Warning!!! Can\'t translate text because not installed module cURL on the server.</i></b>');
    }

    // Validation request.
    if (!$responseJSON) {
        return separatorTitleAndPost($text, '<b style = "color:red"><i>Warning!!! Can\'t translate text because Yandex not answer.</i></b>');
    }

    // Decode JSON to Array
    $response = json_decode($responseJSON, true);

    // Validation JSON obj on response.
    if (json_last_error() !== JSON_ERROR_NONE) {
        return separatorTitleAndPost($text, '<b style = "color:red"><i> Warning!!! Can\'t translate text.Yandex returned not correct answer </i></b>');
    }

    // Validation status translate of Yandex.
    if ($response["code"] !== 200) {
        return separatorTitleAndPost($text, '<b style = "color:red"><i> Warning!!! Can\'t translate text. Yandex reports: ' . $response["message"] . '</i></b>');
    }

    // Output translate text.
    $textTranslated = $response["text"];
    return separatorTitleAndPost($textTranslated[0], YANDEX_SIGNATURE);
}


function separatorTitleAndPost($mainText, $addText) {
    if (current_filter() == 'the_content') {
        return $mainText . $addText;
    } else {
        return $mainText;
    }
}

// Now we will set the call our function when receiving from BD title and content.

add_filter('the_title','getTranslatedTextOfMarshak');
add_filter('the_content','getTranslatedTextOfMarshak');
