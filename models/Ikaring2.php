<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\Json;

class Ikaring2 extends Model
{
    private $http;
    private $cookies;

    public function init()
    {
        parent::init();
        $this->http = new \Zend\Http\Client();
        $this->http->setOptions([
            'maxredirects'  => 0,
            'timeout'       => 60,
            'useragent'     => 'Mozilla/5.0',
            'storeresponse' => false,
            'sslcafile'     => '/etc/pki/tls/cert.pem',
        ]);
        $this->cookies = new \Zend\Http\Cookies();
        $this->cookies->reset();
    }

    public function login() : bool
    {
        if (!$apiTokens = $this->login1()) {
            return false;
        }
        if (!$tokens = $this->login2($apiTokens)) {
            return false;
        }
        if (!$accessToken = $this->login3($tokens)) {
            return false;
        }
        return $this->login4($accessToken);
    }

    public function fetchSchedules() : ?string
    {
        $resp = $this->httpRequest(
            'https://app.splatoon2.nintendo.net/api/schedules',
            'GET',
            true,
            [],
            null,
            ['Accept' => 'application/json']
        );
        if (!$resp->isSuccess()) {
            return null;
        }
        return $resp->getBody();
    }

    public function fetchStages() : ?string
    {
        $resp = $this->httpRequest(
            'https://app.splatoon2.nintendo.net/api/data/stages',
            'GET',
            true,
            [],
            null,
            ['Accept' => 'application/json']
        );
        if (!$resp->isSuccess()) {
            return null;
        }
        return $resp->getBody();
    }

    // ログイン実装 {{{
    private function login1() : ?array
    {
        // {{{
        $params = Yii::$app->params['splatnet'];
        $resp = $this->httpRequest(
            'https://accounts.nintendo.com/connect/1.0.0/api/token',
            'POST',
            false,
            [],
            Json::encode([
                'client_id' => $params['client_id'],
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer-session-token',
                'session_token' => $params['init_session_token'],
            ]),
            ['Accept' => 'application/json']
        );
        if ($resp->isSuccess()) {
            return Json::decode($resp->getBody());
        }
        return null;
        // }}}
    }

    private function login2(array $apiTokens) : ?array
    {
        // {{{
        $resp = $this->httpRequest(
            'https://api-lp1.znc.srv.nintendo.net/v1/Account/GetToken',
            'POST',
            false,
            [],
            Json::encode([
                'parameter' => [
                    'language' => 'null',
                    'naBirthday' => 'null',
                    'naCountry' => 'null',
                    'naIdToken' => $apiTokens['id_token'],
                ],
            ]),
            [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $apiTokens['access_token'],
            ]
        );
        if ($resp->isSuccess()) {
            return Json::decode($resp->getBody())['result'];
        }
        return null;
        // }}}
    }

    private function login3(array $tokens) : ?string
    {
        // {{{
        $params = Yii::$app->params['splatnet'];
        $resp = $this->httpRequest(
            'https://api-lp1.znc.srv.nintendo.net/v1/Game/GetWebServiceToken',
            'POST',
            false,
            [],
            Json::encode([
                'parameter' => [
                    'id' => $params['resource_id'],
                ],
            ]),
            [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $tokens['webApiServerCredential']['accessToken'],
            ]
        );
        if ($resp->isSuccess()) {
            return Json::decode($resp->getBody())['result']['accessToken'];
        }
        return null;
        // }}}
    }

    private function login4(string $accessToken) : bool
    {
        // {{{
        $resp = $this->httpRequest(
            'https://app.splatoon2.nintendo.net/',
            'GET',
            false,
            ['lang' => 'en-US'],
            null,
            [
                'Accept' => 'application/json',
                'X-gamewebtoken' => $accessToken,
            ]
        );
        return $resp->isSuccess();
        // }}}
    }
    // }}}

    private function httpRequest(
        $url,
        string $method = 'GET',
        $followRedirect = 10,
        array $getParams = [],
        $postParams = null,
        array $headers = []) : \Zend\Http\Response
    {
        // {{{
        if ($followRedirect === true) {
            $followRedirect = 10;
        } elseif ($followRedirect === false) {
            $followRedirect = 0;
        }
        $client = $this->http;
        $client->resetParameters();
        $client->setUri($url);
        $client->setMethod($method);
        $client->setParameterGet($getParams);
        if ($postParams !== null) {
            if (is_array($postParams)) {
                $client->setParameterPost($postParams);
            } else {
                $client->setRawBody($postParams);
                $client->setEncType('application/json');
            }
        }
        $client->clearCookies();
        foreach ($this->cookies->getMatchingCookies($client->getUri()) as $cookie) {
            $client->addCookie($cookie);
        }
        $client->setHeaders($headers);
        $this->stderr(sprintf("[httpRequest] %s %s\n", $method, $client->getUri()));
        $response = $client->send();
        foreach (preg_split('/\x0d\x0a|\x0d|\x0a/', $client->getLastRawRequest()) as $line) {
            $this->stderr(sprintf("[httpRequest] > %s\n", $line));
        }
        $this->stderr(sprintf("[httpRequest] => %s\n", $response->renderStatusLine()));
        foreach (preg_split('/\x0d\x0a|\x0d|\x0a/', $response->getHeaders()->toString()) as $line) {
            $this->stderr(sprintf("[httpRequest] < %s\n", $line));
        }
        $this->cookies->addCookiesFromResponse($response, $client->getUri());
        if ($response->isRedirect() && $response->getHeaders()->has('Location') && $followRedirect > 0) {
            $location = $response->getHeaders()->get('Location');
            if (strtolower(substr($location, 0, 5)) === 'http:' ||
                strtolower(substr($location, 0, 6)) === 'https:'
            ) {
                // ok
            } elseif (substr($location, 0, 1) === '/') {
                $client->getUri()->setPath($location);
                $location = $client->getUri();
            } else {
                throw new \Exception('Server returns redirect status with ugly location: ' . $location);
            }
            return $this->httpRequest(
                $location,
                'GET',
                $followRedirect - 1,
                [],
                [],
                $accessToken
            );
        }
        return $response;
        // }}}
    }

    private function stderr(string $text) : void
    {
        fwrite(STDERR, $text);
    }
}
