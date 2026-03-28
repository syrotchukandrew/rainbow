<?php

namespace AppBundle\Controller\Admin;
use AppBundle\Controller\AppController;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Security("is_granted('ROLE_MANAGER')")
 * @Route("/admin")
 */
class UserController extends AppController
{
    /**
     * @Route("/users", name="admin_users")
     * @Method("GET")
     */
    public function usersAction(Request $request)
    {
        $users = $this->getDoctrine()->getRepository(\AppBundle\Entity\User::class)->findAll();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $users,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('@App/admin/user/users.html.twig', array('pagination' => $pagination));
    }

    /**
     * @Route("/users/managers", name="admin_users_managers")
     * @Method("GET")
     */
    public function usersManagersAction(Request $request)
    {
        $users = $this->getDoctrine()->getRepository(\AppBundle\Entity\User::class)->findByRole('ROLE_MANAGER');
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $users,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('@App/admin/user/users.html.twig', array('pagination' => $pagination));
    }

    /**
     * @Route("/estates/{slug}", name="admin_estates_manager")
     */
    public function showEstatesManagerAction(Request $request, $slug)
    {
        $estates = $this->getDoctrine()->getManager()->getRepository(\AppBundle\Entity\Estate::class)
            ->getEstatesOfManager($slug);
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $estates,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('@App/admin/user/estates_manager.html.twig', array('pagination' => $pagination));    }

    /**
     * @Route("/users/lock_user/{username}", name="lock_user")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function lockUserAction(Request $request, $username)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository(\AppBundle\Entity\User::class)->findOneBy(array('username' => $username));
        $user->setEnabled(false);
        $entityManager->flush();
        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/users/unlock_user/{username}", name="unlock_user")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function unlockUserAction(Request $request, $username)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository(\AppBundle\Entity\User::class)->findOneBy(array('username' => $username));
        $user->setEnabled(true);
        $entityManager->flush();
        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/users/do_manager/{username}", name="do_manager")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function doManagerAction(Request $request, $username)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository(\AppBundle\Entity\User::class)->findOneBy(array('username' => $username));
        $user->addRole('ROLE_MANAGER');
        $entityManager->flush();
        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/users/do_user/{username}", name="do_user")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function doUserAction(Request $request, $username)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository(\AppBundle\Entity\User::class)->findOneBy(array('username' => $username));
        if ($user->hasRole('ROLE_MANAGER')) {
            $user->removeRole('ROLE_MANAGER');
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_users');
    }
}

