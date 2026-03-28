<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Estate;
use AppBundle\Entity\File;
use AppBundle\Utils\FileManager;
use AppBundle\Utils\FinalCategoryFinder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\EstateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Security("is_granted('ROLE_MANAGER')")
 * @Route("/admin")
 */
class AdminEstateController extends AbstractController
{
    private ManagerRegistry $doctrine;
    private PaginatorInterface $paginator;
    private FinalCategoryFinder $finalCategoryFinder;
    private FileManager $fileManager;

    public function __construct(
        ManagerRegistry $doctrine,
        PaginatorInterface $paginator,
        FinalCategoryFinder $finalCategoryFinder,
        FileManager $fileManager
    ) {
        $this->doctrine = $doctrine;
        $this->paginator = $paginator;
        $this->finalCategoryFinder = $finalCategoryFinder;
        $this->fileManager = $fileManager;
    }

    /**
     * @Route("/", name="admin_index")
     */
    public function indexAction(Request $request)
    {
        $users = $this->doctrine->getRepository(\AppBundle\Entity\User::class)->findAll();
        $districts = $this->doctrine->getRepository(\AppBundle\Entity\District::class)->findAll();
        $comments = $this->doctrine->getRepository(\AppBundle\Entity\Comment::class)->getDisabledComments();
        $estates = $this->doctrine->getRepository(\AppBundle\Entity\Estate::class)->findAll();
        return $this->render('@App/admin/index.html.twig', array(
            'count_disabled_comments' => count($comments),
            'count_estates' => count($estates),
            'count_users' => count($users),
            'count_districts' => count($districts),
        ));
    }

    /**
     * @Route("/estates", name="admin_estates", methods={"GET"})
     */
    public function estatesAction(Request $request)
    {
        $estates = $this->doctrine->getRepository(\AppBundle\Entity\Estate::class)->getEstatesWithAll();
        $pagination = $this->paginator->paginate(
            $estates,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('@App/admin/estate/estates.html.twig', array('pagination' => $pagination));
    }

    /**
     * @Route("/estate/show/{slug}", name="admin_estate_show", methods={"GET"})
     */
    public function estateShowAction($slug, Request $request)
    {
        $estate = $this->doctrine->getRepository(\AppBundle\Entity\Estate::class)->getOneEstateWithAll($slug);
        $deleteForm = $this->createDeleteForm($estate);
        return $this->render('@App/admin/estate/show_estate.html.twig', array(
            'estate' => $estate,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/estate/new", name="admin_estate_new", methods={"GET", "POST"})
     */
    public function newEstateAction(Request $request)
    {
        $entityManager = $this->doctrine->getManager();
        $estate = new Estate();
        $finalCategories = $this->finalCategoryFinder->findFinalCategories();
        $this->denyAccessUnlessGranted('create', $estate);
        $form = $this->createForm(EstateType::class, $estate, array('categories_choices' => $finalCategories))
            ->add('saveAndCreateNew', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $estate = $this->fileManager->fileManager($estate);
            $entityManager->persist($estate);
            $entityManager->flush();
            $nextAction = $form->get('saveAndCreateNew')->isClicked()
                ? 'admin_estate_new'
                : 'admin_estates';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('@App/admin/estate/new_estate.html.twig', array(
            'form' => $form->createView(),
            'estate' => $estate
        ));
    }

    /**
     * @Route("/estate/edit/{slug}", name="admin_estate_edit", methods={"GET", "POST"})
     */
    public function estateEditAction($slug, Request $request)
    {
        $estate = $this->doctrine->getRepository(\AppBundle\Entity\Estate::class)->getOneEstateWithAll($slug);
        $entityManager = $this->doctrine->getManager();
        $this->denyAccessUnlessGranted('edit', $estate);
        $finalCategories = $this->finalCategoryFinder->findFinalCategories();
        $editForm = $this->createForm(EstateType::class, $estate, array(
            'categories_choices' => $finalCategories, 'isDeleteImages' => true));
        $deleteForm = $this->createDeleteForm($estate);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $estate = $this->fileManager->fileManager($estate);
            $entityManager->persist($estate);
            $entityManager->flush();
            return $this->redirectToRoute('admin_estate_show', array('slug' => $estate->getSlug()));
        }
        return $this->render('@App/admin/estate/edit_estate.html.twig', array(
            'estate' => $estate,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/estate/delete/{slug}", name="admin_estate_delete", methods={"DELETE"})     * @Security("is_granted('remove', estate)")
     * @ParamConverter("estate", options={"mapping": {"slug": "slug"}})
     */
    public function estateDeleteAction(Request $request, Estate $estate)
    {
        $this->denyAccessUnlessGranted('remove', $estate);
        $form = $this->createDeleteForm($estate);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($estate);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_estates');
    }

    private function createDeleteForm(Estate $estate)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_estate_delete', array('slug' => $estate->getSlug())))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * @Route("/do_main_foto/{slug}/{id}", name="do_main_foto", methods={"GET"})     * @ParamConverter("estate", class="AppBundle\Entity\Estate", options={"mapping": {"slug": "slug"}})
     * @ParamConverter("file", class="AppBundle\Entity\File", options={"mapping": {"id": "id"}})
     */
    public function doMainFotoAction(Estate $estate, File $file, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $estate);
        $entityManager = $this->doctrine->getManager();
        $estate->setMainFoto($file);
        $entityManager->flush();

        return $this->redirectToRoute('admin_estate_edit', array('slug' => $estate->getSlug()));
    }

}
