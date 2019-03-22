<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BookController extends Controller
{
    /**
     * @Route("/books", name="book_list")
     * @Template("@App/book/index.html.twig")
     */
    public function indexAction()
    {
        $books = [
            1 =>'PHP the right way',
            'Upgrading to PHP 7',
            'PHP Best Practices'
        ];

        return ['books' => $books];
    }

    /**
     * @Route("/books/{bookName}", name="book_view", requirements={"id": "[0-9]+"})
     * @Template("@App/book/show.html.twig")
     * @param $bookName
     * @return array
     */
    public function showAction($bookName)
    {
        return ['bookName' => $bookName];
    }
}