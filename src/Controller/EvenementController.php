<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/evenement")
 */
class EvenementController extends AbstractController
{
    /**
     * @Route("/", name="evenement_index", methods={"GET"})
     */
    public function index(EvenementRepository $evenementRepository): Response
    {
        $families = $this->getUser()->getFamilies();
        $evenementsArray = array();

        foreach ($families as $family) {
            $evenements = $family->getEvenements()->getValues();
            foreach ($evenements as $evenement){

                array_push ($evenementsArray, $evenement);
            }
        }
        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenementsArray,
        ]);
    }

    /**
     * @Route("/ajout", name="evenement_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);

        $form->handleRequest($request);        
        
        if ($form->isSubmitted() && $form->isValid()) {
            $evenement->addPerson($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($evenement);
            $entityManager->flush();

            $this->addFlash('success', 'Evenement ajouté');
            return $this->redirectToRoute('evenement_index');
        }

        return $this->render('evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/details/{id}", name="evenement_show", requirements= {"id": "\d+"}, methods={"GET"})
     */
    public function show(Evenement $evenement): Response
    {
        $this->denyAccessUnlessGranted('show', $evenement);

        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    /**
     * @Route("/{id}", name="evenement_edit", requirements= {"id": "\d+"}, methods={"GET", "POST"})
     */
    public function edit(Request $request, Evenement $evenement): Response
    {
        $this->denyAccessUnlessGranted('edit', $evenement);

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('evenement_index');
        }

        return $this->render('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="evenement_delete", requirements= {"id": "\d+"}, methods={"DELETE"})
     */
    public function delete(Request $request, Evenement $evenement): Response
    {
        $this->denyAccessUnlessGranted('delete', $evenement);

        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($evenement);
            $entityManager->flush();
        }
        $this->addFlash('danger', 'Evenement supprimé');

        return $this->redirectToRoute('evenement_index');
    }


    // /**
    //  * @Route("/calendar", name="evenement_calendar", methods={"GET"})
    //  */
    // public function calendar(): Response
    // {
    //     return $this->render('evenement/calendar.html.twig');
    // }
}
