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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lotgd\Core\Http\Response;
use Lotgd\CoreBundle\OccurrenceBundle\OccurrenceDispatcher;
use Lotgd\CoreBundle\OccurrenceBundle\Service\Occurrence;

return static function (ContainerConfigurator $container)
{
    $container->services()
        //-- Service
        ->set(Occurrence::class)
            ->args([[]])

        ->set(OccurrenceDispatcher::class)
            ->args([new ReferenceConfigurator(Occurrence::class) ])
            ->call('setResponse', [new ReferenceConfigurator(Response::class)])
            ->lazy()
        ->alias('occurrence_dispatcher', OccurrenceDispatcher::class)
            ->public()
    ;
};
