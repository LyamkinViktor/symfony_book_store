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
        /*
        Stripe::setApiKey("sk_test_2kKmRAbpAQf5q60aaNomaDu000Y2RcIDSd");

        $webhook = WebhookEndpoint::create([
            "url" => "http://lyamkin.personal.dev7.sibers.com/w/",
            "enabled_events" => ["charge.failed", "charge.succeeded"]
        ]);

        //dump($webhook);

        // Retrieve the request's body and parse it as JSON:
        $input = @file_get_contents('');
        $event_json = json_decode($input);

        // Do something with $event_json
        dump($event_json);

        // Return a response to acknowledge receipt of the event
        http_response_code(200); // PHP 5.4 or greater

        */

        return $this->render('@App/webhook/index.html.twig');
    }
}