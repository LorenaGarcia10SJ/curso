<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\Usuario;
use App\Entity\Asignacion;
use App\Repository\CursoRepository;
use App\Repository\AsignacionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CursoController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_dashboard")
     */
    public function adminDashboard(Request $request, EntityManagerInterface $em): Response
    {
        $seccion = $request->query->get('seccion', 'agregar');

        // ✔️ Procesar formulario de edición de curso
        if ($request->isMethod('POST') && $request->request->has('editar_curso_id')) {
            $idCurso = $request->request->get('editar_curso_id');
            $curso = $em->getRepository(Curso::class)->find($idCurso);

            if ($curso) {
                $curso->setNombre($request->request->get('nombre_editado'));
                $curso->setDescripcion($request->request->get('descripcion_editada'));
                $em->flush();
                $this->addFlash('success', 'Curso actualizado correctamente');
            } else {
                $this->addFlash('error', 'Curso no encontrado');
            }

            return $this->redirectToRoute('admin_dashboard', ['seccion' => 'listar']);
        }

        // ✔️ Procesar formulario de creación de curso
        if ($request->isMethod('POST') && $seccion === 'agregar') {
            $nombreCurso = $request->request->get('nombre_curso');
            $descripcion = $request->request->get('descripcion');

            if (!empty($nombreCurso)) {
                $cursoExistente = $em->getRepository(Curso::class)->findOneBy(['nombre' => $nombreCurso]);

                if ($cursoExistente) {
                    $this->addFlash('error', 'Ya existe un curso con ese nombre');
                } else {
                    $curso = new Curso();
                    $curso->setNombre($nombreCurso);
                    $curso->setDescripcion($descripcion);

                    $em->persist($curso);
                    $em->flush();

                    $this->addFlash('success', 'Curso creado exitosamente');
                }
            } else {
                $this->addFlash('error', 'El nombre del curso no puede estar vacío');
            }

            return $this->redirectToRoute('admin_dashboard', ['seccion' => 'agregar']);
        }

        // ✔️ Cargar cursos activos
        $cursos = $em->getRepository(Curso::class)->findActivos();

        return $this->render('admin/dashboard.html.twig', [
            'seccion' => $seccion,
            'cursos' => $cursos,
            'asignaciones' => [],
        ]);
    }


    /**
     * @Route("/admin/eliminarCurso", name="eliminar_curso", methods={"POST"})
     */
    public function eliminarCurso(Request $request, CursoRepository $cursoRepository, EntityManagerInterface $em): Response
    {
        $cursoId = $request->request->get('curso_id');
        $curso = $cursoRepository->find($cursoId);

        if (!$curso) {
            $this->addFlash('error', 'Curso no encontrado.');
        } else {
            $curso->setEstado(0); // Marcar como inactivo
            $em->flush();
            $this->addFlash('success', 'Curso eliminado correctamente.');
        }

        return $this->redirectToRoute('admin_dashboard', ['seccion' => 'listar']);
    }



    /**
     * @Route("/admin/verEstudiantes", name="estudiante_curso")
     */
    public function estudianteCurso(AsignacionRepository $asignacionRepository): Response
    {
        $asignaciones = $asignacionRepository->findAllConCursosYUsuarios();

        return $this->render('admin/dashboard.html.twig', [
            'seccion' => 'estudiantes',
            'asignaciones' => $asignaciones,
        ]);
    }
}
