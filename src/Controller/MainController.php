<?php

/**
 * @author Konrad Albrecht <kontakt@konradalbrecht.pl>
 * @copyright Copyright (c) 2018, Konrad Albrecht
 * @version 1.0
 */
namespace App\Controller;

use App\Service\PocketConnection;
use App\Service\PocketWorker;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
    const CONSUMER_KEY = '77753-2699fe29fd000d7223a69c78';

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/pclogin", name="pclogin")
     * @param PocketConnection $pc
     * @return mixed|\Psr\Http\Message\ResponseInterface|\Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pclogin(PocketConnection $pc)
    {
        $pcVerif = $pc->connect(self::CONSUMER_KEY);

        if ($pcVerif instanceof GuzzleException) {
            return $this->render('main/index.html.twig', [
                'controller_name' => $pcVerif->getMessage(),
            ]);
        }

        $this->session->start();
        $this->session->set('code', $pcVerif);

        return $this->redirect('https://getpocket.com/auth/authorize?request_token='.$pcVerif.'&redirect_uri=http://localhost:8000/pcloged');
    }

    /**
     * @Route("/pcloged", name="pcloged")
     */
    public function pcloged(PocketConnection $pc)
    {
        $this->session->start();

        $response = $pc->authorize(self::CONSUMER_KEY);

        $this->session->set('token', $response['access_token']);
        $this->session->set('username', $response['username']);

        return $this->redirectToRoute('pcpanel');
    }

    /**
     * @Route("/pcpanel", name="pcpanel")
     */
    public function pcpanel(PocketWorker $pcw){
        if (!$pcw->verifyConnection()) {
            return $this->redirectToRoute('pclogin');
        }

        $pocketContent = $pcw->getWholePocket(self::CONSUMER_KEY);
        dump($pocketContent);

        return $this->render('pocketrss/index.html.twig', [
            'pocket' => $pocketContent,
        ]);
    }
}
