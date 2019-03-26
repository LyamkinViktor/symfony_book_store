<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Book;
use AppBundle\Entity\Category;
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
        $books = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Book')
            ->findAll();

        return ['books' => $books];
    }

    /**
     * @Route("/books/{id}", name="book_view", requirements={"id": "[0-9]+"})
     * @Template("@App/book/show.html.twig")
     * @param Book $book
     * @return array
     */
    public function showAction(Book $book)
    {
        return ['book' => $book];
    }

    /**
     * @Route("/category/{id}", name="books_by_category")
     * @Template("@App/category/list_by_category.html.twig")
     * @param Category $category
     * @return array
     */
    public function listByCategoryAction(Category $category)
    {
        $books = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Book')
            ->findByCategory($category);

        return ['books' => $books];
    }
}