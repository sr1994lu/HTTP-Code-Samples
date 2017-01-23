<?php

/**
 * Class AccessTokenAuthentication.
 */
class AccessTokenAuthentication
{
    /**
     * @param $OcpApimSubscriptionKey Ocp-Apim-Subscription-Key.
     * @param $authUrl Oauth url.
     *
     * @return string
     */
    public function getTokens($OcpApimSubscriptionKey, $authUrl)
    {
        try {
            $ch = curl_init();

            $options = [
                CURLOPT_URL            => $authUrl,
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Accept: application/jwt',
                    'Content-Length: 0',
                    'Content-Type: application/json',
                    'Ocp-Apim-Subscription-Key: '.$OcpApimSubscriptionKey,
                ],
                CURLOPT_SSL_VERIFYPEER => true,
            ];

            curl_setopt_array($ch, $options);

            $strResponse = curl_exec($ch);
            $curlErrno = curl_errno($ch);

            if ($curlErrno) {
                $curlError = curl_error($ch);
                throw new Exception($curlError);
            }

            curl_close($ch);

            return $strResponse;
        } catch (Exception $e) {
            echo 'Exception-'.$e->getMessage();
        }
    }
}

/*
 * Class:HTTPTranslator
 *
 * Processing the translator request.
 */

class HTTPTranslator
{
    /*
     * Create and execute the HTTP CURL request.
     *
     * @param string $url        HTTP Url.
     * @param string $authHeader Authorization Header string.
     * @param string $postData   Data to post.
     *
     * @return string.
     *
     */
    public function curlRequest($url, $authHeader)
    {
        //Initialize the Curl Session.
        $ch = curl_init();
        //Set the Curl url.
        curl_setopt($ch, CURLOPT_URL, $url);
        //Set the HTTP HEADER Fields.
        curl_setopt($ch, CURLOPT_HTTPHEADER, [$authHeader, 'Content-Type: text/xml']);
        //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        //Execute the  cURL session.
        $curlResponse = curl_exec($ch);
        //Get the Error Code returned by Curl.
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            $curlError = curl_error($ch);
            throw new Exception($curlError);
        }
        //Close a cURL session.
        curl_close($ch);

        return $curlResponse;
    }
}

try {
    $OcpApimSubscriptionKey = 'your secret key';
    $authUrl = 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken';

    $authObj = new AccessTokenAuthentication();
    $accessToken = $authObj->getTokens($OcpApimSubscriptionKey, $authUrl);

    $authHeader = 'Authorization: Bearer '.$accessToken;

    $fromLanguage = 'ja';
    $toLanguage = 'en';
    $inputStr = 'お寿司食べたい';
    $contentType = 'text/plain';
    $category = 'general';

    $params = 'text='.urlencode($inputStr).'&to='.$toLanguage.'&from='.$fromLanguage;
    $translateUrl = "https://api.microsofttranslator.com/v2/http.svc/Translate?$params";

    //Create the Translator Object.
    $translatorObj = new HTTPTranslator();

    //Get the curlResponse.
    $curlResponse = $translatorObj->curlRequest($translateUrl, $authHeader);

    //Interprets a string of XML into an object.
    $xmlObj = simplexml_load_string($curlResponse);
    foreach ((array) $xmlObj[0] as $val) {
        $translatedStr = $val;
    }
    echo '<table border=2px>';
    echo '<tr>';
    echo "<td><b>From $fromLanguage</b></td><td><b>To $toLanguage</b></td>";
    echo '</tr>';
    echo '<tr><td>'.$inputStr.'</td><td>'.$translatedStr.'</td></tr>';
    echo '</table>';
} catch (Exception $e) {
    echo 'Exception: '.$e->getMessage().PHP_EOL;
}

/*
 * Create and execute the HTTP CURL request.
 *
 * @param string $url        HTTP Url.
 * @param string $authHeader Authorization Header string.
 * @param string $postData   Data to post.
 *
 * @return string.
 *
 */
function curlRequest($url, $authHeader, $postData = '')
{
    //Initialize the Curl Session.
    $ch = curl_init();
    //Set the Curl url.
    curl_setopt($ch, CURLOPT_URL, $url);
    //Set the HTTP HEADER Fields.
    curl_setopt($ch, CURLOPT_HTTPHEADER, [$authHeader, 'Content-Type: text/xml']);
    //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if ($postData) {
        //Set HTTP POST Request.
        curl_setopt($ch, CURLOPT_POST, true);
        //Set data to POST in HTTP "POST" Operation.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
    //Execute the  cURL session.
    $curlResponse = curl_exec($ch);
    //Get the Error Code returned by Curl.
    $curlErrno = curl_errno($ch);
    if ($curlErrno) {
        $curlError = curl_error($ch);
        throw new Exception($curlError);
    }
    //Close a cURL session.
    curl_close($ch);

    return $curlResponse;
}
