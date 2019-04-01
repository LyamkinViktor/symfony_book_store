<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Author;
use AppBundle\Entity\Book;
use AppBundle\Entity\Category;
use AppBundle\Form\BookType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

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
        return [
            'book' => $book,
        ];
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

    /**
     * @Route("/author/{id}", name="books_by_author")
     * @Template("@App/author/list_by_author.html.twig")
     * @param Author $author
     * @return array
     */
    public function listByAuthorAction(Author $author)
    {
        $books = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Book')
            ->findByAuthor($author);

        return ['books' => $books];
    }


    /**
     * @Route("/addBook", name="add_book")
     * @Template("@App/book/add_book.html.twig")
     * @param Request $request
     * @return RedirectResponse|Response
     * @var UploadedFile
     */
    public function addBookAction(Request $request)
    {
        $book = new Book();

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $file */
            $file = $book->getImage();

            $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );
            } catch (FileException $e) {
            }

            $book->setImage($fileName);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('book_list'));
        }

        return $this->render('@App/book/add_book.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }
}