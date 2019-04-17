<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Subscription;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stripe\Event;
use Stripe\Stripe;
use Stripe\StripeObject;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class WebhookController extends Controller
{
    /**
     * @Route("/webhooks/stripe", name="weebhook_stripe")
     * @param Request $request
     * @throws Exception
     */
    public function stripeWebhookAction(Request $request)
    {
        //Set secret key
	    try{
            Stripe::setApiKey($this->getParameter('stripe_secret_key'));
        } catch (Exception $e) {
            file_put_contents(__DIR__ . 'log.txt',$e->getMessage() . "\n", FILE_APPEND);
	        throw new Exception($e->getMessage());
        }

        //Get an associative array of data
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            throw new Exception('Bad JSON body from Stripe!');
        }

        //Get event Id from data
        $eventId = $data['id'];
	
	    //Get the stripe event by id
        /** @var Event $stripeEvent */
        $stripeEvent = $this->findEvent($eventId);


        //Processing events from stripe
        switch ($stripeEvent->type) {
            case 'customer.subscription.deleted':

                // Fully cancel the user's subscription
		        file_put_contents(
		            __DIR__ . 'log.txt',
                    'stripeEvent: ' . $stripeEvent->type . "\n", FILE_APPEND
                );

                $subscriptionId = $stripeEvent->data->object->id;
                file_put_contents(
                    __DIR__ . 'log.txt',
                    'subscriptionId: ' . $subscriptionId . "\n", FILE_APPEND
                );

                $subscription = $this->findSubscription($subscriptionId);

                //Check subscription from table
    		    //$subscriptionFromTable = $subscription->getSubscriptionId();
    		    //file_put_contents(__DIR__ . 'log.txt','subscriptionFromTable: ' . $subscriptionFromTable . "\n", FILE_APPEND);

                file_put_contents(
                    __DIR__ . 'log.txt',
                    'subscriptionStatus: ' . $stripeEvent->data->object->status . "\n", FILE_APPEND
                );

                $subscriptionStatus = $stripeEvent->data->object->status;
                $this->fullyCancelSubscription($subscription, $subscriptionStatus);
                break;

                default:
                    throw new Exception('Unexpected webhook from stripe' . $stripeEvent->type);
        }

    }

    /**
     * @param $eventId
     * @return StripeObject
     */
    public function findEvent($eventId)
    {
        return Event::retrieve($eventId);
    }

    /**
     * @param $subscriptionId
     * @return Subscription|object|null
     * @throws Exception
     */
    private function findSubscription($subscriptionId)
    {
        $subscription = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Subscription')
            ->findOneBy([
                'subscriptionId' => $subscriptionId
            ]);

        if (!$subscription) {
            throw new Exception('Somehow we have no subscription id ' . $subscriptionId);
        }

        return $subscription;
    }

    /**
     * @param Subscription $subscription
     * @param $status
     */
    public function fullyCancelSubscription(Subscription $subscription, $status)
    {
	    $subscription->setStatus($status);
        $subscription->cancel();
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($subscription);
        $entityManager->flush();
    }

}