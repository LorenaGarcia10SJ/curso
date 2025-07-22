<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EstudianteController extends AbstractController
{
    /**
     * @Route("/estudiante", name="estudiante_dashboard")
     */
    public function index(Request $request): Response
    {
        $rol = $request->getSession()->get('rol');

        if ($rol !== 'estudiante') {
            return $this->redirectToRoute('login_form');
        }

        return $this->render('estudiante/dashboard.html.twig');
    }
}
