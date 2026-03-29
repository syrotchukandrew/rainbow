<?php

namespace AppBundle\Controller\Admin;

use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_MANAGER')]
#[Route('/admin')]
class UserController extends AbstractController
{
    private ManagerRegistry $doctrine;
    private PaginatorInterface $paginator;

    public function __construct(ManagerRegistry $doctrine, PaginatorInterface $paginator)
    {
        $this->doctrine = $doctrine;
        $this->paginator = $paginator;
    }

    #[Route('/users', name: 'admin_users', methods: ['GET'])]
    public function usersAction(Request $request)
    {
        $users = $this->doctrine->getRepository(\AppBundle\Entity\User::class)->findAll();
        $pagination = $this->paginator->paginate(
            $users,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('admin/user/users.html.twig', array('pagination' => $pagination));
    }

    #[Route('/users/managers', name: 'admin_users_managers', methods: ['GET'])]
    public function usersManagersAction(Request $request)
    {
        $users = $this->doctrine->getRepository(\AppBundle\Entity\User::class)->findByRole('ROLE_MANAGER');
        $pagination = $this->paginator->paginate(
            $users,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('admin/user/users.html.twig', array('pagination' => $pagination));
    }

    #[Route('/estates/{slug}', name: 'admin_estates_manager')]
    public function showEstatesManagerAction(Request $request, $slug)
    {
        $estates = $this->doctrine->getManager()->getRepository(\AppBundle\Entity\Estate::class)
            ->getEstatesOfManager($slug);
        $pagination = $this->paginator->paginate(
            $estates,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('admin/user/estates_manager.html.twig', array('pagination' => $pagination));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/users/lock_user/{username}', name: 'lock_user', methods: ['GET'])]
    public function lockUserAction(Request $request, $username)
    {
        $entityManager = $this->doctrine->getManager();
        $user = $this->doctrine->getRepository(\AppBundle\Entity\User::class)->findOneBy(array('username' => $username));
        $user->setEnabled(false);
        $entityManager->flush();
        return $this->redirectToRoute('admin_users');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/users/unlock_user/{username}', name: 'unlock_user', methods: ['GET'])]
    public function unlockUserAction(Request $request, $username)
    {
        $entityManager = $this->doctrine->getManager();
        $user = $this->doctrine->getRepository(\AppBundle\Entity\User::class)->findOneBy(array('username' => $username));
        $user->setEnabled(true);
        $entityManager->flush();
        return $this->redirectToRoute('admin_users');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/users/do_manager/{username}', name: 'do_manager', methods: ['GET'])]
    public function doManagerAction(Request $request, $username)
    {
        $entityManager = $this->doctrine->getManager();
        $user = $this->doctrine->getRepository(\AppBundle\Entity\User::class)->findOneBy(array('username' => $username));
        $user->addRole('ROLE_MANAGER');
        $entityManager->flush();
        return $this->redirectToRoute('admin_users');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/users/do_user/{username}', name: 'do_user', methods: ['GET'])]
    public function doUserAction(Request $request, $username)
    {
        $entityManager = $this->doctrine->getManager();
        $user = $this->doctrine->getRepository(\AppBundle\Entity\User::class)->findOneBy(array('username' => $username));
        if ($user->hasRole('ROLE_MANAGER')) {
            $user->removeRole('ROLE_MANAGER');
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_users');
    }
}
