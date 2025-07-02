<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/manifeste', name: 'manifeste')]
    public function manifeste(): Response
    {
        return $this->render('manifeste.html.twig');
    }

    #[Route('/charte', name: 'charte')]
    public function charte(): Response
    {
        return $this->render('charte.html.twig');
    }

    #[Route('/cercles', name: 'cercles')]
    public function cercles(): Response
    {
        return $this->render('cercles.html.twig');
    }

    #[Route('/projets', name: 'projets')]
    public function projets(): Response
    {
        return $this->render('projets.html.twig');
    }

    #[Route('/arbre', name: 'arbre')]
    public function arbre(): Response
    {
        return $this->render('arbre.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('contact.html.twig');
    }
}
