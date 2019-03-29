<?php


namespace AppBundle\Controller;

use AppBundle\Entity\Feedback;
use AppBundle\Form\FeedbackType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FeedbackController extends Controller
{
    /**
     * @Route("/feedback/{id}", name="feedback")
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function feedbackAction(Request $request, $id)
    {

        $feedback = new Feedback();
        $form = $this->createForm(FeedbackType::class, $feedback);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $bookRepository = $em->getRepository('AppBundle:Book');
            $book = $bookRepository->find($id);
            $feedback->setBook($book);

            $em->persist($feedback);
            $em->flush();

            $this->addFlash('success', 'Saved');
            return $this->redirectToRoute('book_view', ['id' => $id]);
        }

        return $this->render('@App/default/feedback.html.twig', [
            'feedback_form' => $form->createView(),
        ]);
    }

}