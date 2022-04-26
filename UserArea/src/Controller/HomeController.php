<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {

        $loginState = $this->_checkLoginState();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'loginState' => $loginState,
        ]);
    }

    private function _checkLoginState(): bool {
        if($this->getUser()) {
            return true;
        }

        return false;
    }
}
