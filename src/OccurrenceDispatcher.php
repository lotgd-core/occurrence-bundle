<?php

/**
 * This file is part of "LoTGD Core Package - Occurrence".
 *
 * @see https://github.com/lotgd-core/occurrence-bundle
 *
 * @license https://github.com/lotgd-core/occurrence-bundle/blob/master/LICENSE.txt
 * @author IDMarinas
 *
 * @since 0.1.0
 */

namespace Lotgd\CoreBundle\OccurrenceBundle;

use Lotgd\Core\Http\Response;
use Lotgd\CoreBundle\OccurrenceBundle\Service\Occurrence;
use Symfony\Component\EventDispatcher\GenericEvent;

class OccurrenceDispatcher
{
    private $listeners = [];
    private $occurrences;
    private $response;

    public function __construct(Occurrence $occurrence)
    {
        $this->occurrences = $occurrence;
    }

    /**
     * @param string      $occurrence Name of occurrence (any defined lotgd_occurrence config)
     * @param object|null $event      An object of Event (Optional)
     * @param array|null  $parameters Parameters to event (Optional)
     */
    public function dispatch(string $occurrence, ?object $event = null, array $parameters = [])
    {
        $event = new GenericEvent($event, $parameters);
        $event->setArgument('skip_description', false); //-- For areas that have description

        $chance = $this->occurrences->getZoneProbability($occurrence);
        $random = random_int(0, 10000);

        if ($chance < 1 || 0 == $random || $random > $chance)
        {
            return $event;
        }

        $audience = $this->getListeners($occurrence);

        if ( ! empty($audience))
        {
            $this->callListeners($audience, $occurrence, $event, $this->occurrences->getZoneMaxActivations($occurrence));
        }

        return $event;
    }

    public function getListeners($occurrence = null): array
    {
        if (null !== $occurrence)
        {
            if (empty($this->listeners[$occurrence]))
            {
                return [];
            }

            return $this->listeners[$occurrence];
        }

        return array_filter($this->listeners);
    }

    public function addListener($occurrence, $listener, int $probability, int $priority)
    {
        if ( ! \in_array($priority, [
            OccurrenceSubscriberInterface::PRIORITY_INFO,
            OccurrenceSubscriberInterface::PRIORITY_INTERACTIVE,
            OccurrenceSubscriberInterface::PRIORITY_ANSWER,
        ]))
        {
            new \InvalidArgumentException('Priority must be one of "OccurrenceSubscriberInterface::PRIORITY_INFO, OccurrenceSubscriberInterface::PRIORITY_INTERACTIVE or OccurrenceSubscriberInterface::PRIORITY_ANSWER"');
        }

        $this->listeners[$occurrence][] = [$listener, min(10000, max(0, $probability)), $priority];
    }

    public function addSubscriber(OccurrenceSubscriberInterface $subscriber)
    {
        foreach ($subscriber->getSubscribedOccurrences() as $occurrence => $params)
        {
            if (\is_string($params[0]))
            {
                $this->addListener($occurrence, [$subscriber, $params[0]], $params[1], $params[2]);
            }
            else
            {
                foreach ($params as $listener)
                {
                    $this->addListener($occurrence, [$subscriber, $listener[0]], $listener[1], $listener[2]);
                }
            }
        }
    }

    public function setResponse(Response $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Triggers the listeners of an occurrence.
     *
     * This method can be overridden to add functionality that is executed
     * for each listener.
     *
     * @param callable[]   $listeners      The occurrence listeners
     * @param string       $occurrence     The name of the occurrence to dispatch
     * @param GenericEvent $event          The event object to pass to the occurrence handlers/listeners
     * @param int          $maxActivations Max number of activations of occurrences for zone
     */
    protected function callListeners(iterable $listeners, string $occurrence, GenericEvent $event, int $maxActivations)
    {
        $raw = 0;

        array_walk($listeners, function ($listener) use (&$raw)
        {
            $raw += $listener[1];
        });

        $inProgress  = false;
        $activations = 0;
        $selected    = $this->filterEvents($listeners, $raw);

        $oneListenerIsInteractive = false;

        foreach ($selected as $listener)
        {
            if ($event->isPropagationStopped() || $activations >= $maxActivations)
            {
                break;
            }

            if (
                //-- If Interactive event is active no allow answer events
                ($oneListenerIsInteractive && OccurrenceSubscriberInterface::PRIORITY_ANSWER == $listener[2])
                //-- Only one interactive event can execute
                || $oneListenerIsInteractive
            ) {
                continue;
            }

            $inProgress = true;

            ++$activations;

            $oneListenerIsInteractive = (OccurrenceSubscriberInterface::PRIORITY_INTERACTIVE == $listener[2]) ? true : $oneListenerIsInteractive;

            $listener[0]($event, $occurrence);

            //-- If Occurrence is a answer break the loop.
            if (OccurrenceSubscriberInterface::PRIORITY_ANSWER == $listener[2])
            {
                //-- By default stop propagation of the event.
                $event->stopPropagation();

                break;
            }
        }

        $event->setArgument('skip_description', $inProgress);

        if ($inProgress)
        {
            $this->response->pageTitle('title.special', [], 'partial_event');
        }
    }

    private function filterEvents($listeners, $raw): array
    {
        $sum = 0;

        return array_filter($listeners, function ($ele) use (&$sum, $raw)
        {
            $chance = mt_rand(1, 10000);
            $normalize = round(($ele[1] / $raw) * 100, 3) * 100;

            if ($chance < $sum || $chance > ($sum + $normalize))
            {
                $sum += $normalize;

                return false;
            }

            return true;
        });
    }
}
