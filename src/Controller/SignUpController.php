<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\SignUpType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SignUpController extends BaseController
{

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityNames[0] = "Registro";
        $this->entityNames[1] = "Registro";
        $this->entityNames[2] = 0;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/sign-up", name="sign_up")
     */
    public function index(Request $request,ValidatorInterface $validator): Response
    {
        $user = new Users();
        $form = $this->createForm(SignUpType::class, $user);
        $form->handleRequest($request);
        $errorMessages = [];
        $campos_validados = false;

        $all = $request->request->all();
        if(isset($all['sign_up']['username'])
            && isset($all['sign_up']['password'])) {

            $input = [
                'username' => $all['sign_up']['username'],
                'password' => $all['sign_up']['password'],
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


            if ($form->isSubmitted() && $form->isValid()
                && $campos_validados == true && sizeof($errorMessages) == 0) {

                $entityManager = $this->getDoctrine()->getManager();

                //comprobar si el  usuario ya existe
                $repositorio = $entityManager->getRepository(Users::class);
                $users = $repositorio->findBy(array("username" => $input['username']));
                if(sizeof($users) > 0)
                {
                    return $this->redirectToRoute('sign_up_error', [], Response::HTTP_SEE_OTHER);
                }
                ///

                $user->setCreatedAt(new \DateTimeImmutable('now'));
                $user->setRoles(array("ROLE_ADMIN"));
                $user->setPassword($this->passwordEncoder->encodePassword($user, $input['password']));
                $user->setActive(1);

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('sign_up_success', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('sign_up/index.html.twig', [
            'user' => $user,
            'form' => $form,
            'entityNames' => $this->entityNames,
            'appURL' => $this->getAppURL(),
            'errors' => $errorMessages,
            'controllerName' => $this->controllerName,
        ]);
    }

    /**
     * @Route("/sign-up/success", name="sign_up_success")
     */
    public function success() : Response
    {
        return $this->render('sign_up/success.html.twig', [
            'appURL' => $this->getAppURL(),
            'entityNames' => $this->entityNames,
            'controllerName' => $this->controllerName,
        ]);
    }

    /**
     * @Route("/sign-up/error", name="sign_up_error")
     */
    public function error() : Response
    {
        return $this->render('sign_up/error.html.twig', [
            'appURL' => $this->getAppURL(),
            'entityNames' => $this->entityNames,
            'controllerName' => $this->controllerName,
        ]);
    }
}
