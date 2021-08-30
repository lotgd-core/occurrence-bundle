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

namespace Lotgd\CoreBundle\OccurrenceBundle\DependencyInjection\Compiler;

use Lotgd\CoreBundle\OccurrenceBundle\OccurrenceDispatcher;
use Lotgd\CoreBundle\OccurrenceBundle\OccurrenceSubscriberInterface;
use Lotgd\CoreBundle\OccurrenceBundle\Service\Occurrence;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged services for an occurrence dispatcher.
 */
class RegisterOccurrence implements CompilerPassInterface
{
    protected $dispatcherService;
    protected $subscriberTag;

    public function __construct(
        string $dispatcherService = 'occurrence_dispatcher',
        string $subscriberTag = 'lotgd_core.occurrence_subscriber'
    ) {
        $this->dispatcherService = $dispatcherService;
        $this->subscriberTag     = $subscriberTag;
    }

    public function process(ContainerBuilder $container)
    {
        if ( ! $container->hasDefinition($this->dispatcherService) && ! $container->hasAlias($this->dispatcherService))
        {
            return;
        }

        $definition           = $container->findDefinition($this->dispatcherService);
        $extractingDispatcher = new ExtractingOccurrenceDispatcher(new Occurrence());

        foreach ($container->findTaggedServiceIds($this->subscriberTag, true) as $id => $attributes)
        {
            $def = $container->getDefinition($id);

            $class = $def->getClass();

            if ( ! $r = $container->getReflectionClass($class))
            {
                throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }
            if ( ! $r->isSubclassOf(OccurrenceSubscriberInterface::class))
            {
                throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, OccurrenceSubscriberInterface::class));
            }
            $class = $r->name;

            ExtractingOccurrenceDispatcher::$subscriber = $class;
            $extractingDispatcher->addSubscriber($extractingDispatcher);

            foreach ($extractingDispatcher->listeners as $args)
            {
                $args[1] = [new Reference($id), $args[1]];
                $definition->addMethodCall('addListener', $args);
            }

            $extractingDispatcher->listeners = [];
        }
    }
}

/**
 * @internal
 */
class ExtractingOccurrenceDispatcher extends OccurrenceDispatcher implements OccurrenceSubscriberInterface
{
    public $listeners = [];

    public static $subscriber;

    public function addListener($eventName, $listener, int $probability, int $priority)
    {
        if ( ! in_array($priority, [
            OccurrenceSubscriberInterface::PRIORITY_INFO,
            OccurrenceSubscriberInterface::PRIORITY_INTERACTIVE,
            OccurrenceSubscriberInterface::PRIORITY_ANSWER
        ])) {
            new \InvalidArgumentException('Priority must be one of "OccurrenceSubscriberInterface::PRIORITY_INFO, OccurrenceSubscriberInterface::PRIORITY_INTERACTIVE or OccurrenceSubscriberInterface::PRIORITY_ANSWER"');
        }

        $this->listeners[] = parent::addListener($eventName, $listener[1], $probability, $priority);
    }

    public static function getSubscribedOccurrences(): array
    {
        $events = [];

        foreach ([self::$subscriber, 'getSubscribedOccurrences']() as $eventName => $params)
        {
            $events[$eventName] = $params;
        }

        return $events;
    }
}
