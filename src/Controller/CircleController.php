<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CircleController extends AbstractController
{
    #[Route('/cercles', name: 'app_cercles')]
    public function index(): Response
    {
        // Exemple de donnÃ©es statiques, Ã  remplacer par une vraie rÃ©cupÃ©ration BDD
        $cercles = [
            ['nom' => 'Cercle Ã‰thique', 'description' => 'Discussions et actions autour des valeurs fondamentales.'],
            ['nom' => 'Cercle Ã‰cologie', 'description' => 'Projets liÃ©s Ã  la protection et au respect du vivant.'],
            ['nom' => 'Cercle Innovation', 'description' => 'Recherche de solutions technologiques et sociales.']
        ];

        return $this->render('cercles.html.twig', [
            'cercles' => $cercles
        ]);
    }
}

