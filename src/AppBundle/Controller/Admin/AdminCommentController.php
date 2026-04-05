<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 04.03.16
 * Time: 22:24
 */

declare(strict_types=1);

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Comment;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_MANAGER')]
#[Route('/admin')]
class AdminCommentController extends AbstractController
{
    private ManagerRegistry $doctrine;
    private PaginatorInterface $paginator;

    public function __construct(ManagerRegistry $doctrine, PaginatorInterface $paginator)
    {
        $this->doctrine = $doctrine;
        $this->paginator = $paginator;
    }

    #[Route('/comments', name: 'admin_comments', methods: ['GET'])]
    public function indexAction(Request $request): Response
    {
        return $this->render('admin/comment/comments.html.twig');
    }

    #[Route('/comments/all', name: 'admin_all_comments', methods: ['GET'])]
    public function allCommentsAction(Request $request): Response
    {
        $comments = $this->doctrine->getRepository(\AppBundle\Entity\Comment::class)->findAllComments();
        $pagination = $this->paginator->paginate(
            $comments,
            $request->query->getInt('page', 1),
            20
        );
        return $this->render('admin/comment/all_comments.html.twig', array('pagination' => $pagination));
    }

    #[Route('/comments/published', name: 'admin_published_comments', methods: ['GET'])]
    public function publishedCommentsAction(Request $request): Response
    {
        $comments = $this->doctrine->getRepository(\AppBundle\Entity\Comment::class)->getEnabledComments();
        $pagination = $this->paginator->paginate(
            $comments,
            $request->query->getInt('page', 1),
            20
        );
        return $this->render('admin/comment/published_comments.html.twig', array('pagination' => $pagination));
    }

    #[Route('/comment/show/{id}', name: 'admin_comment_show', methods: ['GET'])]
    public function showCommentAction(Request $request, Comment $comment): Response
    {
        $form = $this->deleteForm($comment);
        return $this->render("admin/comment/show_comment.html.twig", array("comment" => $comment,
            'delete_form' => $form->createView(),));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/comment_enable/{id}', name: 'admin_enable_comment', methods: ['GET', 'POST'])]
    public function enableCommentAction(Request $request, Comment $comment): RedirectResponse
    {
        $comment->setEnabled(true);
        $em = $this->doctrine->getManager();
        $em->flush();
        return $this->redirectToRoute('admin_comments');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/comment/delete/{id}', name: 'admin_comment_delete', methods: ['DELETE'])]
    public function deleteCommentAction(Request $request, Comment $comment): RedirectResponse
    {
        $form = $this->deleteForm($comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_comments');
    }

    private function deleteForm(Comment $comment)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_comment_delete', array('id' => $comment->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }

    #[Route('', name: 'count_disables_comments', methods: ['GET'])]
    public function countDisablesCommentsAction(Request $request): Response
    {
        $comments = $this->doctrine->getRepository(\AppBundle\Entity\Comment::class)->getDisabledComments();
        return $this->render("admin/comment/count_disables_comment.html.twig",
            array("count_disables_comments" => count($comments)));
    }
}
