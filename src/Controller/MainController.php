<?php

namespace App\Controller;


use App\Form\ContactUsType;
use App\Repository\GradeRepository;
use App\Repository\NoteRepository;;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }

     /**
     * @Route("notre-equipe", name="about")
     */
    public function about(){

        return $this->render('about.html.twig');
    }

    /**
     * @Route("/nous-contacter", name="contact_us")
     */
    public function contactUs(Request $request, MailerInterface $mailer){

        $form = $this->createForm(ContactUsType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form['email']->getData();
            $objet = $form['objet']->getData();
            $message = $form['message']->getData();

            $email = (new Email())
                    ->to('hislife.contact@gmail.com')
                    ->subject($objet)
                    ->html('<h1>Message de '.$email.'<p>: '.$message.'</p>');

                    $mailer->send($email);

                    $this->addFlash('success', 'Votre demande à bien été envoyé ');

                    return $this->redirectToRoute('contact_us');
        }

        return $this->render('contact-us.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mentions-legales", name="mentions_legales")
     */
    public function mentionsLegales(){

        return $this->render('mentions-legales.html.twig');
    }

    /**
     * @Route("/cgu", name="cgu")
     */
    public function cgu(){

        return $this->render('cgu.html.twig');
    }

    /**
     * @Route("/tableaudebord", name="dashboard")
     */
    public function dashboard()
    {   
        if ($this->getUser() !== null) {

            $familiesOfUser = $this->getUser()->getFamilies();

            if ($familiesOfUser->isEmpty() === true) {
                $this->addFlash('danger', 'Vous n\'avez pas ajouter de famille. Vous pouvez le faire dans votre profil, Section Famille');
            }

            return $this->render('home/dashboard.html.twig', [
                'families' => $familiesOfUser
            ]);
        }

        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("tableaudebord/notre-equipe", name="dashboard_about")
     */
    public function aboutDashboard(){

        return $this->render('about_dashboard.html.twig');
    }

    /**
     * @Route("tableaudebord/nous-contacter", name="dashboard_contact_us")
     */
    public function contactUsDashboard(Request $request, MailerInterface $mailer){

        $form = $this->createForm(ContactUsType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form['email']->getData();
            $objet = $form['objet']->getData();
            $message = $form['message']->getData();

            $email = (new Email())
                    ->to('hislife.contact@gmail.com')
                    ->subject($objet)
                    ->html('<h1>Message de '.$email.'<p>: '.$message.'</p>');

                    $mailer->send($email);

                    $this->addFlash('success', 'Votre demande à bien été envoyé ');

                    return $this->redirectToRoute('dashboard');

        }

        return $this->render('contact_us_dashboard.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("tableaudebord/mentions-legales", name="dashboard_mentions_legales")
     */
    public function mentionsLegalesDashboard(){

        return $this->render('mentions_legales_dashboard.html.twig');
    }

    /**
     * @Route("tableaudebord/cgu", name="dashboard_cgu")
     */
    public function cguDashboard(){

        return $this->render('cgu_dashboard.html.twig');
    }

    /**
    * @Route("/ajax", name="ajax_action")
    */
    public function ajaxAction(Request $request)
    {
        if ($this->getUser() !== false) {
            $familiesOfUser = $this->getUser()->getFamilies();
            $childrenArray = array();
            
            foreach ($familiesOfUser as $family) {

                // ### LAST CHILD FAMILY PICTURE ### 
                $lastFamilyPictureArray = [];

                if ($family->getPictures()->last() != false) {
                    $lastFamilyPicture = $family->getPictures()->last();
                    $lastFamilyPictureArray = [
                        'id' => $lastFamilyPicture->getId(),
                        'title' => $lastFamilyPicture->getTitle(),
                        'description' => $lastFamilyPicture->getDescription(),
                        'file' => $lastFamilyPicture->getFile(),
                        'created_at' => $lastFamilyPicture->getCreatedAt()
                    ];
                }

                // ### LAST CHILD FAMILY EVENT ### 
                $lastFamilyEventArray = [];

                if ($family->getEvenements()->last() != false) {
                    $lastFamilyEvent = $family->getEvenements()->last();
                    $lastFamilyEventArray = [
                        'id' => $lastFamilyEvent->getId(),
                        'name' => $lastFamilyEvent->getName(),
                        'type' => $lastFamilyEvent->getType(),
                        'created_at' => $lastFamilyEvent->getCreatedAt(),
                        'begin_at' => $lastFamilyEvent->getBeginAt(),
                        'end_at' => $lastFamilyEvent->getEndAt()
                    ];
                }

                if(!empty($family->getChildren()->getValues())) {
                    $childObject = $family->getChildren()->getValues();
                    foreach ($childObject as $child) {
                        // ### GRADES && NOTE ###
                        $lastGrade = $child->getGrades()->last();
                        $lastGradeArray = [];
                        $lastNote = $child->getNotes()->last();
                        $lastNoteArray = [];
                        $lastSchoolEventArray = [];
                        
                        if ($lastGrade != false) {
                            $lastGradeArray = [
                                'id' => $lastGrade->getId(),
                                'name' => $lastGrade->getName(),
                                'file' => $lastGrade->getFile(),
                                'created_at' => $lastGrade->getCreatedAt(),
                                'type' => 'grade'
                            ];
                        }
                        
                        if ($lastNote != false) {
                            $lastNoteArray = [
                                'id' => $lastNote->getId(),
                                'name' => $lastNote->getName(),
                                'file' => $lastNote->getFile(),
                                'created_at' => $lastNote->getCreatedAt(),
                                'type' => 'note'
                            ];
                        }
                        
                        if ($lastGradeArray && $lastNoteArray != false) {
                            if ($lastGradeArray['created_at'] > $lastNoteArray['created_at']) {
                                $lastSchoolEventArray = $lastGradeArray;
                            }
                            else {
                                $lastSchoolEventArray = $lastNoteArray;
                            }
                        }
                        else if ($lastGradeArray != false) {
                            $lastSchoolEventArray = $lastGradeArray;
                        }
                        else {
                            $lastSchoolEventArray = $lastNoteArray;
                        }
                        
                        
                        // ### HEALTHBOOK ###
                        $lastHealthbook = $child->getHealthbooks()->last();
                        $lastHealthbookArray = [];
                        
                        if ($lastHealthbook != false) {
                            $lastHealthbookArray = [
                                'id' => $lastHealthbook->getId(),
                                'name' => $lastHealthbook->getName(),
                                'file' => $lastHealthbook->getFile(),
                                'created_at' => $lastHealthbook->getCreatedAt()
                            ];
                        }
                        
                        $childrenArray [$child->getId()] = [
                            'firstname' => $child->getFirstname(),
                            'lastname' => $child->getLastname(),
                            'gender' => $child->getGender(),
                            'birthdate' => $child->getBirthdate(),
                            'lastSchoolEventArray' => $lastSchoolEventArray,
                            'lastHealthbook' => $lastHealthbookArray,
                            'lastFamilyPicture' => $lastFamilyPictureArray,
                            'lastFamilyEvent' => $lastFamilyEventArray
                        ];
                    }                
                }
            }
            ;
            /* on récupère la valeur envoyée */
            $idSelect = $request->request->get('idSelect');

            if ($idSelect == 0) {
                $selectChild = array_shift($childrenArray);
            }

            else {
                $selectChild = $childrenArray[$idSelect];
            }

            $response = new Response(json_encode($selectChild));
            
            /* On renvoie une réponse encodée en JSON */
            // $response = new Response(json_encode(array($childrenArray)));
            // dd($response);
            
            $response->headers->set('Content-Type', 'application/json');
            
            return $response;
        }
    }

    
}
