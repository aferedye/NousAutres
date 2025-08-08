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
        // Donnees statiques de demonstration
        $cercles = [
            ['nom' => 'Cercle Ethique', 'description' => 'Discussions et actions autour des valeurs fondamentales.'],
            ['nom' => 'Cercle Ecologie', 'description' => 'Projets lies a la protection et au respect du vivant.'],
            ['nom' => 'Cercle Innovation', 'description' => 'Recherche de solutions technologiques et sociales.']
        ];

        return $this->render('cercles.html.twig', [
            'cercles' => $cercles
        ]);
    }
}

