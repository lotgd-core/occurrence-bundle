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

use Lotgd\CoreBundle\OccurrenceBundle\DependencyInjection\Compiler\RegisterOccurrence;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LotgdOccurrenceBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterOccurrence(), PassConfig::TYPE_BEFORE_REMOVING);
    }
}
