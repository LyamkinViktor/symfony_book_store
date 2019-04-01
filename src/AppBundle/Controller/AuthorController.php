<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Author;
use AppBundle\Form\AuthorType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @Route("/addAuthor", name="add_author")
     * @Template("@App/author/add_author.html.twig")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function addAuthorAction(Request $request)
    {
        $author = new Author();

        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($author);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('author_list'));
        }

        return $this->render('@App/author/add_author.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}