<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Task;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TaskController extends Controller
{
    /**
     * @Route("/", name="task.index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager(); // entity-manager laden
        $repository = $em->getRepository(Task::class); // repository laden
        $tasks = $repository->findAll(); // alle aufgaben aus der datenbank lesen

        return $this->render('AppBundle:task:index.html.twig', array( // antwort an den clienten erzeugen
            'tasks' => $tasks, // die liste aller aufgaben an twig weitergeben
        ));
    }

    /**
     * @Route("/task/{task}/done/", name="task.done")
     */
    public function doneAction(Request $request, Task $task) {

        $task->setDone(1); // task als erledigt markieren

        $em = $this->getDoctrine()->getManager(); // entity-manager laden
        $em->persist($task); // task speichern
        $em->flush(); // geänderte daten zur datenbank schicken

        $this->addFlash('success', 'Aufgabe "'.$task->getName().'" als erledigt markiert!'); // erfolgsmeldung für den benutzer erzeugen

        return $this->redirectToRoute('task.index'); // antwort an den clienten erzeugen: weiterleitung zur aufgaben-liste
    }

    /**
     * @Route("task/new/", name="task.add")
     */
    public function addAction(Request $request) {

        $task = new Task(); // leere aufgabe erzeugen

        $form = $this->createFormBuilder($task) // formular-builder erzeugen
            ->add('name', TextType::class, array('required' => false)) // textfeld für den namen hinzufügen
            ->add('save', SubmitType::class, array('label' => 'Aufgabe speichern')) // speichern-button hinzufügen
            ->getForm(); // formular erzeugen

        $form->handleRequest($request); // request an das formular weiterreichen

        if($form->isSubmitted() AND $form->isValid()) { // wenn das formular abgeschickt wurde und korrekt ausgefüllt wurde...

            $task = $form->getData(); // aufgabe aus dem formular holen
            $em = $this->getDoctrine()->getManager(); // entity-manager laden
            $em->persist($task); // task zum speichern markieren
            $em->flush(); // geänderte daten zur datenbank schicken

            $this->addFlash('success', 'Aufgabe "'.$task->getName().'" hinzugefügt'); // erfolgsmeldung für den benutzer erzeugen

            return $this->redirectToRoute('task.index'); // antwort an den clienten erzeugen: weiterleitung zur aufgaben-liste
        }

        return $this->render('AppBundle:task:add.html.twig', array( // antwort an den clienten erzeugen
            'form' => $form->createView(), // formular zur ausgabe an twig weitergeben
        ));
    }

    /**
     * @Route("task/new/controller/", name="task.new.controller")
     */
    public function addInControllerAction(Request $request) {

        $task = new Task();
        $task->setName('Controller-Aufgabe');
        $task->setDone(0);

        $em = $this->getDoctrine()->getManager();
        $em->persist($task);
        $em->flush();

        return $this->render('AppBundle:task:add_controller.html.twig', array(
            'task' => $task,
        ));
    }
}
