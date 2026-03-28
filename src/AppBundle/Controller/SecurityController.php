<?php

namespace AppBundle\Controller;

use AppBundle\Controller\AppController;
use AppBundle\Entity\User;
use AppBundle\Form\PasswordResetRequestType;
use AppBundle\Form\PasswordResetType;
use AppBundle\Form\UserType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecurityController extends AppController
{
    /**
     * @Route("/login", name="security_login_form")
     */
    public function loginAction()
    {
        $helper = $this->get('security.authentication_utils');

        return $this->render('@App/security/login.html.twig', array(
            'last_username' => $helper->getLastUsername(),
            'error' => $helper->getLastAuthenticationError(),
        ));
    }

    /**
     * @Route("/register", name="user_registration")
     */
    public function registerAction(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher)
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
        return $this->render(
            '@App/security/register.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/login_check", name="security_login_check")
     */
    public function loginCheckAction()
    {
        throw new \Exception('This should never be reached!');
    }

    /**
     * @Route("/reset-password", name="security_reset_request")
     */
    public function resetRequestAction(Request $request, ManagerRegistry $doctrine, MailerInterface $mailer)
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

        return $this->render('@App/security/reset_request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reset-password/{token}", name="security_reset_password")
     */
    public function resetPasswordAction(Request $request, string $token, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher)
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

        return $this->render('@App/security/reset_password.html.twig', [
            'form' => $form->createView(),
            'token' => $token,
        ]);
    }
}
