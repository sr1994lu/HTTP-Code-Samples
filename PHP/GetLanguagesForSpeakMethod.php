<?php

class AccessTokenAuthentication
{
    /*
     * Get the access token.
     *
     * @param string $grantType    Grant type.
     * @param string $scopeUrl     Application Scope URL.
     * @param string $clientID     Application client ID.
     * @param string $clientSecret Application client ID.
     * @param string $authUrl      Oauth Url.
     *
     * @return string.
     */
    public function getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl)
    {
        try {
            //Initialize the Curl Session.
            $ch = curl_init();
            //Create the request Array.
            $paramArr = [
                 'grant_type'    => $grantType,
                 'scope'         => $scopeUrl,
                 'client_id'     => $clientID,
                 'client_secret' => $clientSecret,
            ];
            //Create an Http Query.//
            $paramArr = http_build_query($paramArr);
            //Set the Curl URL.
            curl_setopt($ch, CURLOPT_URL, $authUrl);
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, true);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArr);
            //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //Execute the  cURL session.
            $strResponse = curl_exec($ch);
            //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                $curlError = curl_error($ch);
                throw new Exception($curlError);
            }
            //Close the Curl Session.
            curl_close($ch);
            //Decode the returned JSON string.
            $objResponse = json_decode($strResponse);
            if ($objResponse->error) {
                throw new Exception($objResponse->error_description);
            }

            return $objResponse->access_token;
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
    public function curlRequest($url, $authHeader, $postData = '')
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

    /*
     * Create Request XML Format.
     *
     * @param string $languageCode  Language code
     *
     * @return string.
     */
    public function createReqXML($languageCodes)
    {
        //Create the Request XML.
        $requestXml = '<ArrayOfstring xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">';
        if (count($languageCodes) > 0) {
            foreach ($languageCodes as $codes) {
                $requestXml .= "<string>$codes</string>";
            }
        } else {
            throw new Exception('$languageCodes array is empty.');
        }
        $requestXml .= '</ArrayOfstring>';

        return $requestXml;
    }
}

try {
    //Client ID of the application.
    $clientID = 'clientId';
    //Client Secret key of the application.
    $clientSecret = 'ClientSecret';
    //OAuth Url.
    $authUrl = 'https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/';
    //Application Scope Url
    $scopeUrl = 'http://api.microsofttranslator.com';
    //Application grant type
    $grantType = 'client_credentials';

    //Create the AccessTokenAuthentication object.
    $authObj = new AccessTokenAuthentication();
    //Get the Access token.
    $accessToken = $authObj->getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl);
    //Create the authorization Header string.
    $authHeader = 'Authorization: Bearer '.$accessToken;

    //Call the Detect Method.
    $url = 'http://api.microsofttranslator.com/V2/Http.svc/GetLanguagesForSpeak';

    //Create the Translator Object.
    $translatorObj = new HTTPTranslator();

    // Call the curlRequest.
    $curlResponse = $translatorObj->curlRequest($url, $authHeader);

    //Interprets a string of XML into an object.
    $xmlObj = simplexml_load_string($curlResponse);

    $languageCodes = [];
    foreach ($xmlObj->string as $language) {
        $languageCodes[] = $language;
    }

    /*
     * Get the language Names from languageCodes.
     */

    $locale = 'en';
    $getLanguageNamesurl = "http://api.microsofttranslator.com/V2/Http.svc/GetLanguageNames?locale=$locale";

    //Get the Request XML Format.
    $requestXml = $translatorObj->createReqXML($languageCodes);

    //Call the curlReqest.
    $curlResponse = $translatorObj->curlRequest($getLanguageNamesurl, $authHeader, $requestXml);

    //Parse the XML string.
    $xmlObj = simplexml_load_string($curlResponse);
    $i = 0;
    echo '<table border=2px>';
    echo '<tr>';
    echo '<td><b>LanguageCodes</b></td><td><b>Language Names</b></td>';
    echo '</tr>';
    foreach ($xmlObj->string as $language) {
        echo '<tr><td>'.$languageCodes[$i].'</td><td>'.$language.'</td></tr>';
        $i++;
    }
    echo '</table>';
} catch (Exception $e) {
    echo 'Exception: '.$e->getMessage().PHP_EOL;
}
