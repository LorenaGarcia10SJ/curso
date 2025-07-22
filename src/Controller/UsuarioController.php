<?php

namespace App\Controller;

use App\Entity\Usuario; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UsuarioController extends AbstractController
{
    /**
     * @Route("/listarEstudiantes", name="usuarios", methods={"GET"})
     */
    // FUNCIONALIDADES PARA ROL ADMIN
    public function listarEstudiantes(EntityManagerInterface $em): JsonResponse
    {
        // Simulando que tienes un usuario autenticado (esto luego será reemplazado con login real)
        $usuarioAutenticado = $em->getRepository(Usuario::class)->find(1); // ID del usuario logueado (admin)

        // Verificamos si es admin
        if ($usuarioAutenticado->getRol()->getNombre() !== 'admin') {
            return new JsonResponse(['error' => 'Acceso denegado. Solo admins pueden ver estudiantes.'], 403);
        }

        // Obtener estudiantes (usuarios con rol 'estudiante')
        $estudiantes = $em->getRepository(Usuario::class)->findByRolNombre('estudiante');

        $resultado = [];

        foreach ($estudiantes as $estudiante) {
            $resultado[] = [
                'id' => $estudiante->getIdUser(),
                'nombre' => $estudiante->getNombre(),
                'email' => $estudiante->getEmail()
            ];
        }

        return new JsonResponse($resultado);
    }


    // FUNCIONALIDADES PARA ROL ESTUDIANTE
    // - ASIGNAR CURSO
    /**
     * @Route("/asignarse", name="asignarse_curso",  methods={"POST"})
    */

    public function asignarse(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $usuarioId = $data['usuario_id'];
        $cursoId = $data['curso_id'];

        $usuario = $em->getRepository(Usuario::class)->find($usuarioId);
        $curso = $em->getRepository(Curso::class)->find($cursoId);

        if (!$usuario || !$curso) {
            return new JsonResponse(['error' => 'Usuario o curso inválido'], 404);
        }

        $asignacion = new Asignacion();
        $asignacion->setUsuario($usuario);
        $asignacion->setCurso($curso);
        $asignacion->setFechaAsignacion(new \DateTime());

        $em->persist($asignacion);
        $em->flush();

        return new JsonResponse(['mensaje' => 'Curso asignado']);
    }
}