<?php

/**
 * @author Konrad Albrecht <kontakt@konradalbrecht.pl>
 * @copyright Copyright (c) 2018, Konrad Albrecht
 * @version 1.0
 */
namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class ApiConst
 * @package App\Service
 */
class PocketConnection
{
    private $client;
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        $this->client = new Client([
            'base_uri' => 'https://getpocket.com/v3/',
            'timeout'  => 2.0,
        ]);
    }

    public function pocketConnection($adress, $body)
    {
        $headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
            'X-Accept' => 'application/json',
        ];
        $body = json_encode($body);

        try {
            $response = $this->client->request('POST', $adress, [
                'headers' => $headers,
                'body' => $body,
            ])->getBody();

            $token = json_decode($response, true);
        } catch (GuzzleException $exception) {
            $error = $exception;
        }

        return isset($error) ? $error : $token;
    }

    /**
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function connect($consumer_key)
    {
        $response = $this->pocketConnection('oauth/request', [
                'consumer_key' => $consumer_key,
                'redirect_uri' => '/',
        ]);

        return is_array($response) ? $response['code'] : $response;
    }

    /**
     * @param $consumer_key
     * @param $token
     * @return \Exception|GuzzleException|mixed
     */
    public function authorize($consumer_key)
    {
        $this->session->start();
        $code = $this->session->get('code');

        $response = $this->pocketConnection('oauth/authorize', [
            'consumer_key' => $consumer_key,
            'code' => $code,
        ]);

        return $response;
    }
}