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
     * @Route("/webhooks/stripe", name="webhook_stripe")
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

        // Actions depending on webhooks
//        if ($data['type'] === 'invoice.payment_succeeded') {
//            //To do something
//         }

        $stripeEvent = $this->findEvent($eventId);

        /** @var Event $stripeEvent */
        switch ($stripeEvent->type) {
            case 'customer.subscription.deleted':
                $stripeSubscriptionId = $stripeEvent->data->object->id;
                $subscription = $this->findSubscription($stripeSubscriptionId);
                $this->fullyCancelSubscription($subscription);
                break;
            default:
                throw new Exception(
                    'Unexpected webhook type form Stripe! ' . $stripeEvent->type
                );
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

    /**
     * @param $stripeSubscriptionId
     * @return Subscription|object|null
     * @throws Exception
     */
    private function findSubscription($stripeSubscriptionId)
    {
        $subscription = $this->getDoctrine()
            ->getRepository('AppBundle:Subscription')
            ->findOneBy([
                'stripeSubscriptionId' => $stripeSubscriptionId
            ]);
        if (!$subscription) {
            throw new Exception(
                'Somehow we have no subscription id ' . $stripeSubscriptionId
            );
        }
        return $subscription;
    }

    /**
     *
     * @param Subscription $subscription
     */
    public function fullyCancelSubscription(Subscription $subscription)
    {
        $subscription->cancel();
        $this->em->persist($subscription);
        $this->em->flush($subscription);
    }


}