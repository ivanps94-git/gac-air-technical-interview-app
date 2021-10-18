<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CategoriesFixtures extends Fixture
{
    var $client;
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function load(ObjectManager $manager)
    {

        $response = $this->client->request(
            'GET',
            'https://fakestoreapi.com/products/categories'
        );

        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'
        $content = $response->getContent();

        $elementos = json_decode($content);
        foreach($elementos as $e)
        {
            $categoria = new Categories();
            $categoria->setName($e);
            $categoria->setCreatedAt(new \DateTimeImmutable('now'));
            $manager->persist($categoria);
            $manager->flush();
        }

    }
}
