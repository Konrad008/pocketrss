<?php

/**
 * @author Konrad Albrecht <kontakt@konradalbrecht.pl>
 * @copyright Copyright (c) 2018, Konrad Albrecht
 * @version 1.0
 */
namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class PocketWorker
 * @package App\Service
 */
class PocketWorker
{
    private $session;
    private $pcc;

    public function __construct(SessionInterface $session, PocketConnection $pcc)
    {
        $this->session = $session;
        $this->pcc = $pcc;
    }

    private function checksession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->session->start();
        }
    }
    public function verifyConnection()
    {
        $this->checksession();

        return !is_null($this->session->get('token')) && !is_null($this->session->get('code'));
    }

    public function getWholePocket($key)
    {
        $this->checksession();
        $pocket = $this->pcc->pocketConnection('get', [
            'consumer_key' => $key,
            'access_token' => $this->session->get('token'),
            'state' => 'all',
            'detailType' => 'simple',
        ]);

        return $pocket['list'];
    }

}