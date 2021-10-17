<?php

namespace App\Controller;
use App\Entity\Users;
use App\Form\CategoryType;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use Doctrine\ORM\Query\Expr\Base;
use http\Client\Curl\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Categories;

use App\Repository\CategoriesRepository;

/**
 * @Route("/categories")
 */
class CategoriesController extends BaseController
{

    public function __construct(Security $security)
    {
        //Primer elemento nombre en singular, segundo elemento nombre en plural, masculino 0 femenino 1
        $this->entityNames[0] = "Categoría";
        $this->entityNames[1] = "Categorias";
        $this->entityNames[2] = 1;
        $this->controllerName = "Categorías";
        $this->security = $security;
    }


    /**
     * @Route("/", name="categories_index", methods={"GET"})
     */
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        return $this->render('categories/index.html.twig', [
            'categories' => $categoriesRepository->findAll(),
            'appURL' => $this->getAppURL(),
            'entityNames' => $this->entityNames,
            'controllerName' => $this->controllerName,
            'logged_user' => $this->security->getUser(),
        ]);
    }

    /**
     * @Route("/new", name="categories_new", methods={"GET","POST"})
     */
    public function new(Request $request,ValidatorInterface $validator): Response
    {
        $category = new Categories();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        $errorMessages = [];
        $campos_validados = false;

        $all = $request->request->all();
        if(isset($all['category']['name'])) {

            $input = [
                'name' => $all['category']['name'],
            ];

            $constraints = new Assert\Collection([
                'name' => [new Assert\NotBlank],
            ]);

            $violations = $validator->validate($input, $constraints);
            if (count($violations) > 0) {
                $accessor = PropertyAccess::createPropertyAccessor();
                foreach ($violations as $violation) {

                    $accessor->setValue($errorMessages,
                        $violation->getPropertyPath(),
                        $violation->getMessage());
                }
            }
            $campos_validados = true;
        }

        if ($form->isSubmitted() && $form->isValid()
            && $campos_validados == true && sizeof($errorMessages) == 0) {

            $entityManager = $this->getDoctrine()->getManager();
            $category->setCreatedAt(new \DateTimeImmutable('now'));

            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('categories/new.html.twig', [
            'category' => $category,
            'form' => $form,
            'entityNames' => $this->entityNames,
            'appURL' => $this->getAppURL(),
            'errors' => $errorMessages,
            'controllerName' => $this->controllerName,
            'logged_user' => $this->security->getUser(),

        ]);
    }

    /**
     * @Route("/{id}", name="categories_show", methods={"GET"})
     */

    public function show(Categories $category): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repositorio = $entityManager->getRepository(Categories::class);
        $form = $this->createForm(CategoryType::class, $category);
        $category = $repositorio->find($category);
        return $this->renderForm('categories/show.html.twig', [
            'category' => $category,
            'entityNames' => $this->entityNames,
            'appURL' => $this->getAppURL(),
            'controllerName' => $this->controllerName,
            'form' => $form,
            'errors' => [],
            'logged_user' => $this->security->getUser(),
        ]);
    }


    /**
     * @Route("/{id}/edit", name="categories_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Categories $category,ValidatorInterface $validator): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repositorio = $entityManager->getRepository(Categories::class);
        $category = $repositorio->find($category);
        $form = $this->createForm(CategoryType::class, $category);
        $errorMessages = [];
        $campos_validados = false;

        $all = $request->request->all();
        if(isset($all['category']['name'])) {

            $input = [
                'name' => $all['category']['name'],
            ];

            $constraints = new Assert\Collection([
                'name' => [new Assert\NotBlank],
            ]);

            $violations = $validator->validate($input, $constraints);

            if (count($violations) > 0) {
                $accessor = PropertyAccess::createPropertyAccessor();
                foreach ($violations as $violation) {

                    $accessor->setValue($errorMessages,
                        $violation->getPropertyPath(),
                        $violation->getMessage());
                }
            }else{
                $form->handleRequest($request);
            }

            $campos_validados = true;
        }

        if ($form->isSubmitted() && $form->isValid()
            && $campos_validados == true && sizeof($errorMessages) == 0) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('categories/edit.html.twig', [
            'category' => $category,
            'form' => $form,
            'entityNames' => $this->entityNames,
            'appURL' => $this->getAppURL(),
            'errors' => $errorMessages,
            'controllerName' => $this->controllerName,
            'logged_user' => $this->security->getUser(),
        ]);
    }




}
