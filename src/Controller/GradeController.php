<?php

namespace App\Controller;

use App\Entity\Grade;
use App\Form\UploadType;
use App\Form\UploadEditType;
use App\Repository\GradeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

class GradeController extends AbstractController
{
    /** 
     * @Route("/bulletins/index", name="grade_browse")
     */
    public function browse()
    {
        $families = $this->getUser()->getFamilies();
        $gradesArray = array();

        foreach ($families as $family) {
            $children = $family->getChildren()->getValues();

            if (empty($children)) {
                $this->addFlash('danger', 'Vous n\'avez pas ajouté d\'enfant à la famille '.$family.'. Veuillez le faire dans votre profil, section Profil Enfant, avant d\'ajouter un document');
            }

            foreach ($children as $child) {
                $grades = $child->getGrades()->getValues();

                foreach($grades as $grade) {
                    array_push($gradesArray, $grade);
                }
            }
        }
        return $this->render('grade/browse.html.twig', [
            'grades' => $gradesArray,
        ]);
    }

    /** 
     * @Route("/bulletins/details/{id}", name="grade_read", requirements={"id": "\d+"})
     */
    public function read(Grade $grade)
    {
        $this->denyAccessUnlessGranted('read', $grade);

        return $this->render('grade/read.html.twig', [
            'grade' => $grade,
        ]);
    }

    /**
     * @Route("/bulletins/{id}", name="grade_edit", requirements={"id": "\d+"}, methods={"GET", "POST"})
     */
    public function edit(Grade $grade, Request $request, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('edit', $grade);

        $form = $this->createForm(UploadEditType::class, $grade);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()){
            
            $newFile = $form['file']->getData();
            
            if ($newFile != null) {
                function generateRandomString($length = 10)
                {
                    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $maxLength = strlen($characters);
                    $randomString = '';
                    for ($i = 0; $i < $length; $i++) {
                        $randomString .= $characters[rand(0, $maxLength - 1)];
                    }
                    return $randomString;
                }
                
                $fileName = generateRandomString();
                
                $directory = 'assets/files/grades/';
                
                $finalDirectory = $directory.$fileName.'.jpg';
                $grade->setFile($finalDirectory);
                
                $newFile->move($this->getParameter('grades_directory'), $fileName.'.jpg');

            }
            
            $em->persist($grade);
            $em->flush();
            return $this->redirectToRoute('grade_browse');
        }
        
            
        return $this->render('grade/edit.html.twig', [
            'form' => $form->createView(),
            'grade' => $grade
        ]);
    }

    /**
     * @Route("/bulletins/ajouter", name="grade_add")
     */
    public function add(Request $request, EntityManagerInterface $em, NotifierInterface $notifier)
    {
        $grade = new Grade;

        $form = $this->createForm(UploadType::class, $grade);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $name = $form->getData()->getName();
            $file = $form['file']->getData();
            
            function generateRandomString($length = 10)
            {
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $maxLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < $length; $i++)
                {
                    $randomString .= $characters[rand(0, $maxLength - 1)];
                }
                return $randomString;
            }
            
            $fileName = generateRandomString();
            
            $grade->setName($name);
            
            $directory = 'assets/files/grades/';
            
            $finalDirectory = $directory.$fileName.'.jpg';
            $grade->setFile($finalDirectory);
            
            $em->persist($grade);
            
            $file->move($this->getParameter('grades_directory'), $fileName.'.jpg');
            
            $em->flush();
            
            $notification = (new Notification('Nouveau bulletin de note  ajouté sur le site His Life', ['email']))
            ->content('Un nouveau bulletin de note a été ajouté concernant '.$grade->getChild()->getFirstname().' : ' .$grade->getName());
            
            $family = $form->getData()->getChild()->getFamilies()->first();

            $people = $family->getPeople()->getValues();

            foreach ($people as $person) {
                if ($person != $this->getUser()) {
                    $user = $person->getEmail();
                    
                    $recipient = new Recipient(
                        $user,
                    );
                    $notifier->send($notification, $recipient);
                }    
            }

            $this->addFlash('success', 'Bulletin de note ajouté');
            return $this->redirectToRoute('grade_browse');
        }

        return $this->render('grade/add.html.twig', [
            'controller_name' => 'GradeController',
            'form' => $form->createView(),
        ]);
    }

    /** 
     * @Route("/bulletins/{id}", name="grade_delete", requirements= {"id": "\d+"}, methods={"DELETE"})
     */
    public function delete (Request $request, Grade $grade, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('delete', $grade);

        if ($this->isCsrfTokenValid('delete'.$grade->getId(), $request->request->get('_token'))){

        $em = $this->getDoctrine()->getManager();
        $em->remove($grade);
        $em->flush();
        
        $this->addFlash('danger', 'bulletin de note supprimé');
        }
        return $this->redirectToRoute('grade_browse');
    }
}
