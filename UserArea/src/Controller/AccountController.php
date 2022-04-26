<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AccountController extends AbstractController
{
    private HttpClientInterface $client;
    private string $api_url = 'http://127.0.0.1:8001/api/';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $menu = array('user', 'products/edit', 'logout');

        return $this->render('account/index.html.twig', [
            'menu' => $menu,
        ]);
    }

    #[Route('/user', name: 'app_user')]
    public function appUser(): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $response = $this->client->request(
            'GET',
            $this->api_url.'user'
        );

        if($response->getStatusCode() != 200) {
            $this->redirectToRoute('app_account');
        }

        $user = json_decode($response->getContent(), true);

        return $this->render('account/user.html.twig', [
            'user' => $user,
        ]);
    }
}
