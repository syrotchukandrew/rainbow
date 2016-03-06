<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Security("has_role('ROLE_MANAGER')")
 * @Route("/admin")
 */
class UserController extends Controller
{
    /**
     * @Route("/users", name="admin_users")
     * @Method("GET")
     */
    public function usersAction(Request $request)
    {
        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();
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
        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findByRole('ROLE_MANAGER');
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
        $em = $this->getDoctrine()->getManager();
        $estates = $em->getRepository('AppBundle:Estate')->findBy(array('createdBy' => $slug));
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
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function lockUserAction(Request $request, $username)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(array('username' => $username));
        $user->setLocked(true);
        $entityManager->flush();
        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/users/unlock_user/{username}", name="unlock_user")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function unlockUserAction(Request $request, $username)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(array('username' => $username));
        $user->setLocked(false);
        $entityManager->flush();
        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/users/do_manager/{username}", name="do_manager")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function doManagerAction(Request $request, $username)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(array('username' => $username));
        $user->setRoles(array('ROLE_MANAGER'));
        $entityManager->flush();
        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/users/do_user/{username}", name="do_user")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function doUserAction(Request $request, $username)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(array('username' => $username));
        $roles = $user->getRoles();
        if (in_array("ROLE_MANAGER", $roles)) {
            $key = array_search("ROLE_MANAGER", $roles);
            $roles[$key] = 'ROLE_USER';
            $user->setRoles($roles);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_users');
    }
}

