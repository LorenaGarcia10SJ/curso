<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_dashboard")
     */
    public function index(Request $request): Response
    {
        $rol = $request->getSession()->get('rol');

        if ($rol !== 'admin') {
            return $this->redirectToRoute('login_form');
        }

        return $this->render('admin/dashboard.html.twig');
    }
}
