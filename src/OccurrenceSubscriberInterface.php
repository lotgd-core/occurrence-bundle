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

/**
 * An OccurrenceSubscriber knows itself what occurrences it is interested in.
 * If an OccurrenceSubscriber is added to an OccurrenceDispatcherInterface, the manager invokes
 * {@link getSubscribedOccurrences} and registers the subscriber as a listener for all
 * returned occurrences.
 */
interface OccurrenceSubscriberInterface
{
    /**
     * Event that not is interactive or need response from user.
     * Only show information about what happened.
     *
     * This event does NOT INTERRUPT the execution of the interactive and answer events.
     *
     * @var int
     */
    public const PRIORITY_INFO = 0;

    /**
     * Event that is interactive but not need response from user.
     * Example, have comment area when user can write a comment.
     *
     * This event INTERRUPT the execution of the other INTERACTIVE and ANSWER events but not the info events.
     *
     * @var int
     */
    public const PRIORITY_INTERACTIVE = 1;

    /**
     * Event that need a response from user.
     * Displays a navigation menu for the user to provide an answer.
     *
     * This event INTERRUPT the execution of OTHER events.
     *
     * @var int
     */
    public const PRIORITY_ANSWER = 2;

    /**
     * Returns an array of occurrence names this subscriber wants to listen to.
     *
     * The array keys are occurrence names and the value can be:
     *  * An array composed of the method name to call and the probability
     *  * An array of arrays composed of the method names to call and respective probabilities
     *
     * For instance:
     *  * ['occurrenceName' => ['methodName', $probability, $priority]]
     *  * ['occurrenceName' => [['methodName1', $probability, $priority], ['methodName2', $probability, $priority]]]
     *
     * Note:
     *  * Probability is a int between 0 and 10000 (0% and 100.00%)
     *  * Priority is a int defined in constants:
     *      -   OccurrenceSubscriberInterface::PRIORITY_INFO
     *      -   OccurrenceSubscriberInterface::PRIORITY_INTERACTIVE
     *      -   OccurrenceSubscriberInterface::PRIORITY_NEEDS_RESPONSE
     *
     * The code must not depend on runtime state as it will only be called at compile time.
     * All logic depending on runtime state must be put into the individual methods handling the occurrences.
     *
     * @return array<string, mixed> The occurrences names to listen to
     */
    public static function getSubscribedOccurrences();
}
