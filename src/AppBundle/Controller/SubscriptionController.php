<?php


namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    /**
     * @Route("/subscription", name="subscribe")
     * @param Request $request
     * @return Response
     */
    public function subscribeAction(Request $request)
    {
        dump($request);

        return $this->render('@App/subscription/index.html.twig');

    }

}