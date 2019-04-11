<?php


namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stripe\Stripe;
use Stripe\WebhookEndpoint;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WebhookController extends Controller
{
    /**
     * @Route("/webhooks", name="webhooks_page")
     */
    public function indexAction()
    {
        return $this->render('@App/webhook/index.html.twig');
    }
}