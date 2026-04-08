<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\PasswordResetRequestType;
use App\Form\PasswordResetType;
use App\Form\UserType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;

    public function __construct(AuthenticationUtils $authenticationUtils)
    {
        $this->authenticationUtils = $authenticationUtils;
    }

    #[Route('/login', name: 'security_login_form', methods: ['GET'])]
    public function loginAction(): Response
    {
        return $this->render('security/login.html.twig', [
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/register', name: 'user_registration')]
    public function registerAction(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPlainPassword()));
            $user->setEnabled(true);
            $em = $doctrine->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('security_login_form');
        }
        return $this->render('security/register.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/login_check', name: 'security_login_check')]
    public function loginCheckAction(): never
    {
        throw new \Exception('This should never be reached!');
    }

    #[Route('/reset-password', name: 'security_reset_request', methods: ['GET', 'POST'])]
    public function resetRequestAction(Request $request, ManagerRegistry $doctrine, MailerInterface $mailer): Response
    {
        $form = $this->createForm(PasswordResetRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $em = $doctrine->getManager();
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $user->setConfirmationToken($token);
                $user->setPasswordRequestedAt(new \DateTime());
                $em->flush();

                $resetUrl = $this->generateUrl('security_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                $emailMessage = (new Email())
                    ->from(new Address($this->getParameter('mailer_from_address'), $this->getParameter('mailer_from_name')))
                    ->to($user->getEmail())
                    ->subject('Password Reset Request')
                    ->html('<p>To reset your password, please visit: <a href="' . $resetUrl . '">' . $resetUrl . '</a></p><p>This link is valid for 2 hours.</p>');

                $mailer->send($emailMessage);
            }

            $this->addFlash('success', 'security.reset_email_sent');
            return $this->redirectToRoute('security_login_form');
        }

        return $this->render('security/reset_request.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/reset-password/{token}', name: 'security_reset_password', methods: ['GET', 'POST'])]
    public function resetPasswordAction(Request $request, string $token, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher): Response
    {
        $em = $doctrine->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['confirmationToken' => $token]);

        if (!$user || !$user->isPasswordRequestNonExpired(7200)) {
            $this->addFlash('error', 'security.reset_token_invalid');
            return $this->redirectToRoute('security_reset_request');
        }

        $form = $this->createForm(PasswordResetType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPlainPassword()));
            $user->setConfirmationToken(null);
            $user->setPasswordRequestedAt(null);
            $em->flush();

            $this->addFlash('success', 'security.reset_password_success');
            return $this->redirectToRoute('security_login_form');
        }

        return $this->render('security/reset_password.html.twig', ['form' => $form->createView(), 'token' => $token]);
    }
}
