<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Subscription;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stripe\Event;
use Stripe\StripeObject;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    /**
     * @Route("/webhooks/stripe", name="weebhook_stripe")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function stripeWebhookAction(Request $request)
    {	
	//try ->, write catch to log file
	//Set secret key
	\Stripe\Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            throw new Exception('Bad JSON body from Stripe!');
        }
        
        $eventId = $data['id'];
	
	
        /** @var Event $stripeEvent */
        $stripeEvent = \Stripe\Event::retrieve($eventId);

        switch ($stripeEvent->type) {
            case 'customer.subscription.deleted':
		
		file_put_contents(__DIR__ . 'log.txt','stripeEvent: ' . $stripeEvent->type . "\n", FILE_APPEND);
                // todo - fully cancel the user's subscription

                $subscriptionId = $stripeEvent->data->object->id;
                file_put_contents(__DIR__ . 'log.txt','subscriptionId: ' . $subscriptionId . "\n", FILE_APPEND);
                
            
                
                //$subscription = $this->findSubscription($stripeSubscriptionId);
                
                $subscription = $this->getDoctrine()
            		             ->getRepository('AppBundle:Subscription')
                                     ->findOneBy([
                                                'subscriptionId' => $subscriptionId,
                                            ]);
                                            
    		$subscriptionFromTable = $subscription->getSubscriptionId();
    		file_put_contents(__DIR__ . 'log.txt','subscriptionFromTable: ' . $subscriptionFromTable . "\n", FILE_APPEND);
                //file_put_contents(__DIR__ . 'log.txt','subscriptionStatus: ' . $stripeEvent->status . "\n", FILE_APPEND);
                file_put_contents(__DIR__ . 'log.txt','subscriptionStatus: ' . $stripeEvent->data->object->status . "\n", FILE_APPEND);
                //file_put_contents(__DIR__ . 'log.txt','subscriptionStatus: ' . $stripeEvent->data->status . "\n", FILE_APPEND);
                //file_put_contents(__DIR__ . 'log.txt','subscriptionStatus: ' . $stripeEvent->items->status . "\n", FILE_APPEND);
                
                $subscriptionStatus = $stripeEvent->data->object->status;
                $this->fullyCancelSubscription($subscription, $subscriptionStatus);
                break;

                default:
                    throw new Exception('Unexpected webhook from stripe' . $stripeEvent->type);
        }

        return $this->render('@App/webhook/index.html.twig');
    }

    /**
     * @param $eventId
     * @return StripeObject
     */
    public function findEvent($eventId)
    {
        return Event::retrieve($eventId);
    }

    private function findSubscription($stripeSubscriptionId)
    {
        $subscription = $this->getDoctrine()
            ->getRepository('AppBundle:Subscription')
            ->findOneBy([
                'subscriptionId' => $stripeSubscriptionId
            ]);


        if (!$subscription) {
            throw new Exception('Somehow we have no subscription id ' . $stripeSubscriptionId);
        }

        return $subscription;
    }

    public function fullyCancelSubscription(Subscription $subscription, $status)
    {
	$subscription->setStatus($status);
        $subscription->cancel();
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($subscription);
        $entityManager->flush();
    }

}