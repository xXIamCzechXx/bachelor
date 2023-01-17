<?php

namespace App\Controller;

use App\Entity\FormAnswers;
use App\Entity\Pages;
use App\Entity\User;
use App\Form\ContactFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ContactController extends BaseController
{
    /**
     * @Route("/kontakt", name="contact")
     */
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if(!$page = $this->em->getRepository(Pages::class)->findOneBy(['name' => 'contact'])) {
            throw $this->createNotFoundException();
        }

        $contactForm = $this->createForm(ContactFormType::class);
        $contactForm->handleRequest($request);

        if($contactForm->isSubmitted() && $contactForm->isValid()) {

            $user = $this->getUser() ?? null;

            /** @var FormAnswers $answer */
            $answer = $contactForm->getData();
            $answer
                ->setUser($user)
                ->setUserIp($this->container->get('request_stack')->getMasterRequest()->getClientIp());

            $em->persist($answer);
            $em->flush();

            $this->addFlash(FLASH_SUCCESS, 'Děkujeme za dotaz, budeme se snažit ho co nejdříve zpracovat :)');
            return $this->redirectToRoute('contact');
        }

        return $this->render('default/contact/contact.html.twig', [
            'page' => $page,
            'form' => $contactForm->createView(),
        ]);
    }
}
