<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function register(AuthenticationUtils $authenticationUtils): Response
    {

        if($this->getUser()) {
            return $this->redirectToRoute('app_account');
        }

        //Get Auth Error if there is one!
        $error = $authenticationUtils->getLastAuthenticationError();

        //Get the username of the UserAccount Logging in
        $userName = $authenticationUtils->getLastUsername();


        return $this->render('login/index.html.twig', [
            'lastUserName' => $userName,
            'error' => $error,
        ]);
    }
}
