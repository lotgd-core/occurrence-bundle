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

namespace Lotgd\CoreBundle\OccurrenceBundle\DependencyInjection;

use Lotgd\CoreBundle\OccurrenceBundle\OccurrenceSubscriberInterface;
use Lotgd\CoreBundle\OccurrenceBundle\Service\Occurrence;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class LotgdOccurrenceExtension extends ConfigurableExtension
{
    public function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));

        $loader->load('services.php');

        $container->getDefinition(Occurrence::class)
            ->replaceArgument(0, $mergedConfig)
        ;

        $container->registerForAutoconfiguration(OccurrenceSubscriberInterface::class)
            ->addTag('lotgd_core.occurrence_subscriber')
        ;
    }
}
