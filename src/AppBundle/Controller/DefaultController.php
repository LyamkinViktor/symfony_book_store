<?php

namespace AppBundle\Controller;

use AppBundle\Form\FeedbackType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('@App/default/index.html.twig');
    }

    /**
     * @Route("/feedback", name="feedback")
     * @param Request $request
     * @return Response
     */
    public function feedbackAction(Request $request)
    {
        $form = $this->createForm(FeedbackType::class);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $feedback = $form->getData();

            $em = $this->getDoctrine()->getManager();

            $em->persist($feedback);
            $em->flush();

            $this->addFlash('success', 'Saved');
            return $this->redirectToRoute('feedback');
        }

        return $this->render('@App/default/feedback.html.twig', [
            'feedback_form' => $form->createView(),
        ]);
    }
}
