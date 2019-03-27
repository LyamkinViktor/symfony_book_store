<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AuthorController extends Controller
{
    /**
     * @Route("/authors", name="author_list")
     * @Template("@App/author/index.html.twig")
     */
    public function indexAction()
    {
        $authors = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Author')
            ->findBy([], ['name' => 'ASC']);

        return ['authors' => $authors];
    }
}