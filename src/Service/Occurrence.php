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

namespace Lotgd\CoreBundle\OccurrenceBundle\Service;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class Occurrence
{
    private $zones;
    private $propertyAccessor;

    public function __construct(array $zones = [])
    {
        $this->zones = $zones;
    }

    /**
     * Get probability of activation for the given zone.
     */
    public function getZoneProbability(string $zone): ?int
    {
        return $this->propertyAccess()->getValue($this->zones, "[{$zone}][probability]");
    }

    /**
     * Get max activations for zone.
     */
    public function getZoneMaxActivations(string $zone): ?int
    {
        return $this->propertyAccess()->getValue($this->zones, "[{$zone}][max_activation]");
    }

    /**
     * Get all occurrence zones.
     */
    public function getZones(): array
    {
        return $this->zones;
    }

    private function propertyAccess(): PropertyAccessor
    {
        if ( ! $this->propertyAccessor instanceof PropertyAccess)
        {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return $this->propertyAccessor;
    }
}
