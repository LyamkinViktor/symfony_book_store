<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CategoryController extends Controller
{
    /**
     * @Route("/categories", name="category_list")
     * @Template("@App/category/index.html.twig")
     */
    public function indexAction()
    {
        $categories = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Category')
            ->findBy([], ['name' => 'ASC']);

        return ['categories' => $categories];
    }


    /*
    /**
     * @Route("/categories/{id}", name="category_view")
     * @Template("@App/book/show.html.twig")
     * @param Books $book
     * @return array
     */
    /*
    public function showAction(Books $book)
    {
        dump($book);

        return ['book' => $book];
    }
    */
}