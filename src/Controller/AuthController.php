<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Rol;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AbstractController
{

    /**
     * @Route("/login", name="login_form", methods={"GET"})
     */
    public function loginForm(): Response
    {
        // Envía la variable error con valor null para evitar error en Twig
        return $this->render('auth/login.html.twig', [
            'error' => null,
            'email' => null,
        ]);
    }
     /**
     * @Route("/login", name="login_usuario", methods={"POST"})
     */
    public function login(Request $request, EntityManagerInterface $em): Response
    {
        $email = $request->request->get('email', '');
        $contrasena = $request->request->get('contrasena', '');

        $usuario = $em->getRepository(Usuario::class)->findOneBy(['email' => $email]);

        if (!$usuario || $usuario->getContrasena() !== $contrasena) {
            return $this->render('auth/login.html.twig', [
                'error' => 'Credenciales inválidas',
                'email' => $email,
            ]);
        }

        // Guardar datos en sesión para simular login
        $session = $request->getSession();
        $session->set('usuario_id', $usuario->getIdUser());
        $session->set('rol', $usuario->getRol()->getNombre());

        if ($usuario->getRol()->getNombre() === 'admin') {
            return $this->redirectToRoute('admin_dashboard');
        } else {
            return $this->redirectToRoute('estudiante_dashboard');
        }
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(Request $request): Response
    {
        $request->getSession()->clear();
        return $this->redirectToRoute('login_form');
    }
    

    /**
     * @Route("/registrar", name="registro_form", methods={"GET"})
    */
    public function registroForm(): Response
    {
        return $this->render('auth/register.html.twig', [
            'error' => null,
            'success' => null,
        ]);
    }

    /**
     * @Route("/registrar", name="registrar_usuario", methods={"POST"})
    */
    public function registrar(Request $request, EntityManagerInterface $em): Response
    {
        $nombre = $request->request->get('nombre');
        $email = $request->request->get('email');
        $contrasena = $request->request->get('contrasena');

        // Verificar si ya existe un usuario con ese email
        $usuarioExistente = $em->getRepository(Usuario::class)->findOneBy(['email' => $email]);
        if ($usuarioExistente) {
            return $this->render('auth/register.html.twig', [
                'error' => 'El email ya está registrado.',
                'success' => null,
            ]);
        }

        // Obtener el rol "estudiante"
        $rolEstudiante = $em->getRepository(Rol::class)->findOneBy(['nombre' => 'estudiante']);
        if (!$rolEstudiante) {
            return new Response('El rol estudiante no existe en la base de datos.', 500);
        }

        $usuario = new Usuario();
        $usuario->setNombre($nombre);
        $usuario->setEmail($email);
        $usuario->setContrasena($contrasena);
        $usuario->setRol($rolEstudiante);

        $em->persist($usuario);
        $em->flush();

        return $this->render('auth/register.html.twig', [
            'success' => 'Usuario registrado exitosamente. Ya puedes iniciar sesión.',
            'error' => null,
        ]);
    }
}
