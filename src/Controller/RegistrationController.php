<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\ReverifyType;
use App\Security\EmailVerifier;
use App\Security\AppCustomAuthenticator;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
//    private EmailVerifier $emailVerifier;
//
//    public function __construct(EmailVerifier $emailVerifier)
//    {
//        $this->emailVerifier = $emailVerifier;
//    }

    #[Route('/register', name: 'register')]
    public function register(Request $request,
                             UserPasswordEncoderInterface $passwordEncoder,
//                             GuardAuthenticatorHandler $guardHandler,
//                             AppCustomAuthenticator $authenticator,
                             EmailVerifier $emailVerifier): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $emailVerifier->sendEmailConfirmation('verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('registration@arnaudrabel.com', 'Socialize - Email verify'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email


            // auto login after registration is valid
//            return $guardHandler->authenticateUserAndHandleSuccess(
//                $user,
//                $request,
//                $authenticator,
//                'main' // firewall name in security.yaml
//            );

            // redirect because we don't want auto login, we want email verification first
            return $this->redirectToRoute('register_success');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/register/success', name: 'register_success')]
    public function register_success(): Response
    {
        return $this->render('registration/success.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }

    #[Route('/verifyemail', name: 'verify_email')]
    public function verifyUserEmail(Request $request,
                                    UserRepository $userRepository,
                                    EmailVerifier $emailVerifier): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            if ($exception->getReason() === 'The link to verify your email has expired. Please request a new link.') {
                return $this->redirectToRoute('reverify');
            }

            return $this->redirectToRoute('register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified, you can now login.');

        return $this->redirectToRoute('login');
    }

    #[Route('/register/reverify', name: 'reverify')]
    public function reverify(Request $request,
                             EmailVerifier $emailVerifier,
                             EntityManagerInterface $em): Response
    {

        $form = $this->createForm(ReverifyType::class,);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form->getData();


            /**
             * @var $user User
             */
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {

                // generate a signed url and email it to the user
                $emailVerifier->sendEmailConfirmation('verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('registration@arnaudrabel.com', 'Socialize - Email verify'))
                        ->to($user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );

                // redirect because we don't want auto login, we want email verification first
                return $this->redirectToRoute('reverify_success');

            } else {

                //TODO add flashes
                $this->addFlash(
                    'error',
                    'Your email has not been found in our database.'
                );

            }

        }

        return $this->render('registration/reverify.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    #[Route('/register/reverify/success', name: 'reverify_success')]
    public function reverify_success(): Response
    {
        return $this->render('registration/reverify_success.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }

}
