<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Author;
use AppBundle\Entity\Category;
use AppBundle\Form\AuthorType;
use AppBundle\Form\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @Route("/addCategory", name="add_category")
     * @Template("@App/category/add_category.html.twig")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function addCategoryAction(Request $request)
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('category_list'));
        }

        return $this->render('@App/category/add_category.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}