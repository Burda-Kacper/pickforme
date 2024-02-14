<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HomepageController extends AbstractController
{
    /**
     * @return Response
     */
    public function homepage(): Response
    {
        return $this->render('homepage/homepage.html.twig');
    }

    /**
     * @return Response
     */
    public function random(): Response
    {
        return $this->render("homepage/random.html.twig");
    }
}
