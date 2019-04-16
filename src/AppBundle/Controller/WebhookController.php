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
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            throw new Exception('Bad JSON body from Stripe!');
        }

        $eventId = $data['id'];

        /** @var Event $stripeEvent */
        $stripeEvent = $this->findEvent($eventId);

        switch ($stripeEvent->type) {
            case 'customer.subscription.deleted':

                // todo - fully cancel the user's subscription

                $stripeSubscriptionId = $stripeEvent->data->object->id;
                $subscription = $this->findSubscription($stripeSubscriptionId);
                $this->fullyCancelSubscription($subscription);

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

    public function fullyCancelSubscription(Subscription $subscription)
    {
        $subscription->cancel();
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($subscription);
        $entityManager->flush();
    }

}