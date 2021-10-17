<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Products;
use App\Entity\StockHistoric;
use App\Form\CategoryType;
use App\Form\ModifyStockType;
use App\Form\ProductsType;
use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use App\Repository\StockHistoricRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/products")
 */
class ProductsController extends BaseController
{
    public function __construct(Security $security)
    {
        //Primer elemento nombre en singular, segundo elemento nombre en plural, masculino 0 femenino 1
        $this->entityNames[0] = "Producto";
        $this->entityNames[1] = "Productos";
        $this->entityNames[2] = 0;
        $this->controllerName = "Productos";
        $this->security = $security;
    }

    /**
     * @Route("/", name="products_index", methods={"GET"})
     */
    public function index(ProductsRepository $productsRepository): Response
    {
        return $this->render('products/index.html.twig', [
            'products' => $productsRepository->findAll(),
            'appURL' => $this->getAppURL(),
            'entityNames' => $this->entityNames,
            'controllerName' => $this->controllerName,
            'logged_user' => $this->security->getUser(),
        ]);
    }

    /**
     * @Route("/new", name="products_new", methods={"GET","POST"})
     */
    public function new(Request $request,
                        ValidatorInterface $validator,
                        CategoriesRepository $categoriesRepository): Response
    {
        $product = new Products();
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);
        $errorMessages = [];
        $campos_validados = false;

        $all = $request->request->all();
        if(isset($all['products']['name'])
        && isset($all['products']['category'])) {

            $input = [
                'name' => $all['products']['name'],
                'category' => $all['products']['category'],
            ];

            $constraints = new Assert\Collection([
                'name' => [new Assert\NotBlank],
                'category' => [new Assert\NotBlank],
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
            $product->setCreatedAt(new \DateTimeImmutable('now'));
            $product->setStock(0);

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('products/new.html.twig', [
            'product' => $product,
            'form' => $form,
            'entityNames' => $this->entityNames,
            'appURL' => $this->getAppURL(),
            'errors' => $errorMessages,
            'controllerName' => $this->controllerName,
            'logged_user' => $this->security->getUser(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="products_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Products $product,ValidatorInterface $validator): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repositorio = $entityManager->getRepository(Products::class);
        $product = $repositorio->find($product);

        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);
        $errorMessages = [];
        $campos_validados = false;

        $all = $request->request->all();
        if(isset($all['products']['name'])
            && isset($all['products']['category'])) {

            $input = [
                'name' => $all['products']['name'],
                'category' => $all['products']['category'],
            ];

            $constraints = new Assert\Collection([
                'name' => [new Assert\NotBlank],
                'category' => [new Assert\NotBlank],
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

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('products/edit.html.twig', [
            'product' => $product,
            'form' => $form,
            'entityNames' => $this->entityNames,
            'appURL' => $this->getAppURL(),
            'errors' => $errorMessages,
            'controllerName' => $this->controllerName,
            'logged_user' => $this->security->getUser(),
        ]);


    }

    /**
     * @Route("/{id}/modify-stock", name="products_modify_stock", methods={"GET","POST"})
     */
    public function products_modify_stock(Request $request, Products $product,ValidatorInterface $validator): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repositorio = $entityManager->getRepository(Products::class);
        $product = $repositorio->find($product);
        $producto_original = $product;
        $stock_original = $product->getStock();

        $form = $this->createForm(ModifyStockType::class, $product);
        $errorMessages = [];
        $campos_validados = false;

        $all = $request->request->all();
        if(isset($all['modify_stock']['stock'])) {

            $input = [
                'stock' => $all['modify_stock']['stock'],
            ];

            $constraints = new Assert\Collection([
                'stock' => [new Assert\NotBlank],
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

            if($stock_original + intval($product->getStock()) <= 0)
            {
                //Sin stock
                $errorMessages['stock_vacio'] = "No hay suficientes productos para añadir/quitar este stock";
                return $this->renderForm('products/modify_stock.html.twig', [
                    'product' => $producto_original,
                    'form' => $form,
                    'entityNames' => $this->entityNames,
                    'appURL' => $this->getAppURL(),
                    'errors' => $errorMessages,
                    'controllerName' => $this->controllerName,
                    'logged_user' => $this->security->getUser(),
                ]);

            }

            //Registrar modificación stock
            $stock_historic = new StockHistoric();
            $user = $this->security->getUser();
            $stock_historic->setUser($user);
            $stock_historic->setProduct($product);
            $stock_historic->setCreatedAt(new \DateTimeImmutable('now'));
            $stock_historic->setStock($product->getStock());
            $entityManager->persist($stock_historic);
            //

            //Actualizar producto
            $product->setStock($stock_original + $product->getStock());



            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('products/modify_stock.html.twig', [
            'product' => $product,
            'form' => $form,
            'entityNames' => $this->entityNames,
            'appURL' => $this->getAppURL(),
            'errors' => $errorMessages,
            'controllerName' => $this->controllerName,
            'logged_user' => $this->security->getUser(),
        ]);



    }

    /**
     * @Route("/{id}/historic", name="products_historic", methods={"GET"})
     */
    public function historic(Products $product,ProductsRepository $productsRepository,StockHistoricRepository $historicRepository): Response
    {

        $entityManager = $this->getDoctrine()->getManager();
        $repositorio = $entityManager->getRepository(Products::class);
        $product = $repositorio->find($product);

        $repositorio = $entityManager->getRepository(StockHistoric::class);
        $historic = $repositorio->findBy(["product" => $product->getId()]);
        return $this->render('products/historic.html.twig', [
            'product' => $product,
            'historic' => $historic,
            'appURL' => $this->getAppURL(),
            'entityNames' => $this->entityNames,
            'controllerName' => $this->controllerName,
            'logged_user' => $this->security->getUser(),
        ]);
    }

}
