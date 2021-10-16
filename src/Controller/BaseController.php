<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class BaseController extends AbstractController
{
    var $entityNames;
    public function __construct()
    {
        //Primer elemento nombre en singular, segundo elemento nombre en plural, masculino 0 femenino 1
        $this->entityNames = array("","",0);
    }

    public function getAppURL()
    {
        //Forzamos a localhost (podría coger el server name pero en local sin una dns con ips da problemas de CORS)
        $protocol = (!empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) ? 'https://' : 'http://';
        return $protocol."localhost:".$_SERVER['SERVER_PORT'];
    }

}