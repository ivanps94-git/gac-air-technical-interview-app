<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UsersFixtures extends Fixture
{
    var $client;
    private UserPasswordEncoderInterface $passwordEncoder;
    public function __construct(HttpClientInterface $client,UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->client = $client;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {

        $response = $this->client->request(
            'GET',
            'https://fakestoreapi.com/users'
        );

        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'
        $content = $response->getContent();

        $elementos = json_decode($content);
        foreach($elementos as $e)
        {
            $usuario = new Users();
            $usuario->setUsername($e->username);
            $usuario->setPassword($this->passwordEncoder->encodePassword($usuario,$e->password));
            $usuario->setActive(1);
            $usuario->setCreatedAt(new \DateTimeImmutable('now'));
            $usuario->setRoles(array("ROLE_ADMIN"));
            $manager->persist($usuario);
            $manager->flush();
        }

    }
}
