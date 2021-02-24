<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class AppController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }




//    #[Route('/mailtest', name: 'mailtest')]
//    public function mailtest(MailerInterface $mailer): Response
//    {
//
//        $email = (new Email())
//            ->from('registration@arnaudrabel.com')
//            ->to('arnaud.rabel@gmail.com')
////            ->cc('cc@example.com')
//            //->bcc('bcc@example.com')
//            //->replyTo('fabien@example.com')
//            //->priority(Email::PRIORITY_HIGH)
//            ->subject('Socialize - Email verification')
//            ->text('Sending emails is fun again!');
////            ->html('<p>See Twig integration for better HTML integration!</p>');
//
//        try {
//            $mailer->send($email);
//        } catch (TransportExceptionInterface $e) {
//            throw new TransportException();
//        }
//
//
//        return $this->render('app/test/mailtest.html.twig', [
//            'controller_name' => 'AppController',
//        ]);
//    }

}
