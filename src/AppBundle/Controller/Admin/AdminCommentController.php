<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 04.03.16
 * Time: 22:24
 */

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Comment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * @Security("has_role('ROLE_MANAGER')")
 * @Route("/admin")
 */
class AdminCommentController extends Controller
{
    /**
     * @Route("/comments", name="admin_comments")
     */
    public function indexAction(Request $request)
    {
        $comments = $this->getDoctrine()->getRepository('AppBundle:Comment')->getDisabledComments();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $comments,
            $request->query->getInt('page', 1),
            20
        );
        return $this->render('AppBundle::admin/comment/comments.html.twig', array('pagination' => $pagination));
    }

    /**
     * @Route("/comments/all", name="admin_all_comments")
     */
    public function allCommentsAction(Request $request)
    {
        $comments = $this->getDoctrine()->getRepository('AppBundle:Comment')->findAllComments();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $comments,
            $request->query->getInt('page', 1),
            20
        );
        return $this->render('AppBundle::admin/comment/all_comments.html.twig', array('pagination' => $pagination));
    }

    /**
     * @Route("/comments/published", name="admin_published_comments")
     */
    public function publishedCommentsAction(Request $request)
    {
        $comments = $this->getDoctrine()->getRepository('AppBundle:Comment')->getEnabledComments();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $comments,
            $request->query->getInt('page', 1),
            20
        );
        return $this->render('AppBundle:admin/comment:published_comments.html.twig', array('pagination' => $pagination));
    }

    /**
     * @Route("/comment/show/{id}", name="admin_comment_show")
     * @ParamConverter("comment", options={"mapping": {"id": "id"}})
     */
    public function showCommentAction(Request $request, Comment $comment)
    {
        $form = $this->deleteForm($comment);
        return $this->render("AppBundle:admin/comment:show_comment.html.twig", array("comment" => $comment,
            'delete_form' => $form->createView(),));
    }

    /**
     * @Route("/comment_enable/{id}", name="admin_enable_comment")
     * @ParamConverter("comment", options={"mapping": {"id": "id"}})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function enableCommentAction(Request $request, Comment $comment)
    {
        $comment->setEnabled(true);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return $this->redirectToRoute('admin_comments');
    }

    /**
     * @Route("/comment/delete/{id}", name="admin_comment_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("comment", options={"mapping": {"id": "id"}})
     */
    public function deleteCommentAction(Request $request, Comment $comment)
    {
        $form = $this->deleteForm($comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
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

    /**
     * @Route (name="count_disables_comments")
     */
    public function countDisablesCommentsAction(Request $request)
    {
        $comments = $this->getDoctrine()->getRepository('AppBundle:Comment')->getDisabledComments();
        return $this->render("AppBundle:admin/comment:count_disables_comment.html.twig",
            array("count_disables_comments" => count($comments)));
    }
}