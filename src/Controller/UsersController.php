<?php

namespace App\Controller;

use App\Entity\Users;
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

/**
 * @Route("/users")
 */
class UsersController extends BaseController
{

    private UserPasswordEncoderInterface $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder,Security $security)
    {
        //Primer elemento nombre en singular, segundo elemento nombre en plural, masculino 0 femenino 1
        $this->entityNames[0] = "Usuario";
        $this->entityNames[1] = "Usuarios";
        $this->entityNames[2] = 0;
        $this->passwordEncoder = $passwordEncoder;
        $this->controllerName = "Usuarios";
        $this->security = $security;
    }


    /**
     * @Route("/", name="users_index", methods={"GET"})
     */
    public function index(UsersRepository $usersRepository): Response
    {
        return $this->render('users/index.html.twig', [
            'users' => $usersRepository->findAll(),
            'appURL' => $this->getAppURL(),
            'entityNames' => $this->entityNames,
            'controllerName' => $this->controllerName,
            'logged_user' => $this->security->getUser(),
        ]);
    }

    /**
     * @Route("/new", name="users_new", methods={"GET","POST"})
     */
    public function new(Request $request,ValidatorInterface $validator): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);
        $errorMessages = [];
        $campos_validados = false;

        $all = $request->request->all();
        if(isset($all['users']['username'])
            && isset($all['users']['password'])) {

            $input = [
                'username' => $all['users']['username'],
                'password' => $all['users']['password'],
                ];

            $constraints = new Assert\Collection([
                'username' => [new Assert\NotBlank],
                'password' => [new Assert\notBlank],
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
            $user->setCreatedAt(new \DateTimeImmutable('now'));
            $user->setRoles(array("ROLE_ADMIN"));
            $user->setPassword($this->passwordEncoder->encodePassword($user, $input['password']));

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('users/new.html.twig', [
            'user' => $user,
            'form' => $form,
            'entityNames' => $this->entityNames,
            'appURL' => $this->getAppURL(),
            'errors' => $errorMessages,
            'controllerName' => $this->controllerName,
            'logged_user' => $this->security->getUser(),
        ]);
    }

    /**
     * @Route("/{id}", name="users_show", methods={"GET"})
     */

    public function show(Users $user): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repositorio = $entityManager->getRepository(Users::class);
        $form = $this->createForm(UsersType::class, $user);
        $usuario = $repositorio->find($user);
        return $this->renderForm('users/show.html.twig', [
            'user' => $usuario,
            'entityNames' => $this->entityNames,
            'appURL' => $this->getAppURL(),
            'controllerName' => $this->controllerName,
            'form' => $form,
            'errors' => [],
            'logged_user' => $this->security->getUser(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="users_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Users $user,ValidatorInterface $validator): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repositorio = $entityManager->getRepository(Users::class);
        $user = $repositorio->find($user);
        $user->setPassword("");
        $form = $this->createForm(UsersType::class, $user);
        $errorMessages = [];
        $campos_validados = false;

        $all = $request->request->all();
        if(isset($all['users']['username'])
            && isset($all['users']['password'])) {

            $input = [
                'username' => $all['users']['username'],
                'password' => $all['users']['password'],
            ];

            $constraints = new Assert\Collection([
                'username' => [new Assert\NotBlank],
                'password' => [new Assert\notBlank],
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

            return $this->redirectToRoute('users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
            'entityNames' => $this->entityNames,
            'appURL' => $this->getAppURL(),
            'errors' => $errorMessages,
            'controllerName' => $this->controllerName,
            'logged_user' => $this->security->getUser(),
        ]);
    }




}
