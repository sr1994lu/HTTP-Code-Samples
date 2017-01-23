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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
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

    //Set the Params.
    $originalText = 'una importante contribución a la rentabilidad de la empresa';
    $translatedText = 'an important contribution to the company profitability';
    $fromLanguage = 'es';
    $toLanguage = 'en';
    $user = 'TestUser';
    $category = 'general';
    $uri = null;
    $rating = 3;
    $contentType = 'text/plain';

    //Create the string for passing the values through GET method.
    $params = 'originaltext='.urlencode($originalText).
              '&translatedtext='.urlencode($translatedText).
              '&from='.$fromLanguage.
              '&to='.$toLanguage.
              '&user='.$user.
              '&uri='.$uri.
              '&rating='.$rating.
              '&contentType='.$contentType.
              '&categsory='.$category;

    //HTTP AddTranslation URL.
    $addTranslationArr = "http://api.microsofttranslator.com/V2/Http.svc/AddTranslation?$params";

    //Create the Translator Object.
    $translatorObj = new HTTPTranslator();

    //Call the HTTP curl request.
    $translatorObj->curlRequest($addTranslationArr, $authHeader);
    echo "Translation for <b>'$originalText'</b> added successfully.".PHP_EOL;
} catch (Exception $e) {
    echo 'Exception: '.$e->getMessage().PHP_EOL;
}
