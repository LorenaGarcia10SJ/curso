<?php

// src/Controller/EstudianteController.php

namespace App\Controller;

use App\Entity\Asignacion;
use App\Entity\Curso;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EstudianteController extends AbstractController
{
    /**
     * @Route("/estudiante", name="estudiante_dashboard")
     */
    public function dashboard(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $rol = $session->get('rol');
        $usuario_id = $session->get('usuario_id');
        $nombre = $session->get('nombre');

        if ($rol !== 'estudiante') {
            return $this->redirectToRoute('login_form');
        }

        $seccion = $request->query->get('seccion', 'perfil');

        $usuario = $em->getRepository(Usuario::class)->find($usuario_id);
        $cursos = [];
        $asignaciones = [];

        if ($seccion === 'asignarse') {
            $cursos = $em->createQuery(
                'SELECT c FROM App\Entity\Curso c
                 WHERE c.estado = 1 AND c.idCurso NOT IN (
                    SELECT IDENTITY(a.curso)
                    FROM App\Entity\Asignacion a
                    WHERE a.usuario = :usuario
                 )'
            )->setParameter('usuario', $usuario)->getResult();
        }

        if ($seccion === 'asignados') {
            $asignaciones = $em->getRepository(Asignacion::class)->findBy(['usuario' => $usuario]);
        }

        return $this->render('estudiante/dashboard.html.twig', [
            'nombre' => $nombre,
            'seccion' => $seccion,
            'usuario_id' => $usuario_id,
            'cursos' => $cursos,
            'asignaciones' => $asignaciones,
        ]);
    }

    /**
     * @Route("/estudiante/asignar", name="asignar_curso_estudiante", methods={"POST"})
     */
    public function asignarCurso(Request $request, EntityManagerInterface $em): Response
    {
        $usuarioId = $request->request->get('usuario_id');
        $cursoId = $request->request->get('curso_id');

        $usuario = $em->getRepository(Usuario::class)->find($usuarioId);
        $curso = $em->getRepository(Curso::class)->find($cursoId);

        if (!$usuario || !$curso) {
            $this->addFlash('error', 'Usuario o curso inválido');
        } else {
            $yaAsignado = $em->getRepository(Asignacion::class)->findOneBy([
                'usuario' => $usuario,
                'curso' => $curso
            ]);

            if ($yaAsignado) {
                $this->addFlash('error', 'Ya estás asignado a este curso');
            } else {
                $asignacion = new Asignacion();
                $asignacion->setUsuario($usuario);
                $asignacion->setCurso($curso);
                $asignacion->setFechaAsignacion(new \DateTime());

                $em->persist($asignacion);
                $em->flush();

                $this->addFlash('success', 'Curso asignado exitosamente');
            }
        }

        return $this->redirectToRoute('estudiante_dashboard', ['seccion' => 'asignados']);
    }
}
