<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AsignacionController extends AbstractController
{
    /**
     * @Route("/asignacion", name="app_asignacion")
     */
    public function index(): Response
    {
        return $this->render('asignacion/index.html.twig', [
            'controller_name' => 'AsignacionController',
        ]);
    }
}
