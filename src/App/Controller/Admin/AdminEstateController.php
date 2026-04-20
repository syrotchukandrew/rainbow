<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Entity\Estate;
use App\Entity\File;
use App\Form\EstateType;
use App\Utils\FileManager;
use App\Utils\FinalCategoryFinder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_MANAGER')]
#[Route('/admin')]
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

    #[Route('/', name: 'admin_index', methods: ['GET'])]
    public function indexAction(Request $request): Response
    {
        $em = $this->doctrine->getManager();
        $countUsers = (int) $em->createQuery('SELECT COUNT(u.id) FROM App\Entity\User u')->getSingleScalarResult();
        $countDistricts = (int) $em->createQuery('SELECT COUNT(d.id) FROM App\Entity\District d')->getSingleScalarResult();
        $countDisabledComments = $this->doctrine->getRepository(Comment::class)->countDisabledComments();
        $countEstates = (int) $em->createQuery('SELECT COUNT(e.id) FROM App\Entity\Estate e')->getSingleScalarResult();
        return $this->render('admin/index.html.twig', array(
            'count_disabled_comments' => $countDisabledComments,
            'count_estates' => $countEstates,
            'count_users' => $countUsers,
            'count_districts' => $countDistricts,
        ));
    }

    #[Route('/estates', name: 'admin_estates', methods: ['GET'])]
    public function estatesAction(): Response
    {
        return $this->render('admin/estate/estates.html.twig');
    }

    #[Route('/estate/show/{slug}', name: 'admin_estate_show', methods: ['GET'])]
    public function estateShowAction(string $slug, Request $request): Response
    {
        $estate = $this->doctrine->getRepository(Estate::class)->getOneEstateWithAll($slug);
        $deleteForm = $this->createDeleteForm($estate);
        return $this->render('admin/estate/show_estate.html.twig', array(
            'estate' => $estate,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    #[Route('/estate/new', name: 'admin_estate_new', methods: ['GET', 'POST'])]
    public function newEstateAction(Request $request): Response
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
        return $this->render('admin/estate/new_estate.html.twig', array(
            'form' => $form->createView(),
            'estate' => $estate
        ));
    }

    #[Route('/estate/edit/{slug}', name: 'admin_estate_edit', methods: ['GET', 'POST'])]
    public function estateEditAction(string $slug, Request $request): Response
    {
        $estate = $this->doctrine->getRepository(Estate::class)->getOneEstateWithAll($slug);
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
        return $this->render('admin/estate/edit_estate.html.twig', array(
            'estate' => $estate,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    #[Route('/estate/delete/{slug}', name: 'admin_estate_delete', methods: ['DELETE'])]
    public function estateDeleteAction(Request $request, #[MapEntity(mapping: ['slug' => 'slug'])] Estate $estate): Response
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

    #[Route('/do_main_foto/{slug}/{id}', name: 'do_main_foto', methods: ['GET'])]
    public function doMainFotoAction(
        #[MapEntity(mapping: ['slug' => 'slug'])] Estate $estate,
        #[MapEntity(mapping: ['id' => 'id'])] File $file,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('edit', $estate);
        $entityManager = $this->doctrine->getManager();
        $estate->setMainFoto($file);
        $entityManager->flush();

        return $this->redirectToRoute('admin_estate_edit', array('slug' => $estate->getSlug()));
    }
}
