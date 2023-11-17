<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace App\Mapping\Operator\Simple;

use Pimcore\Bundle\DataImporterBundle\Exception\InvalidConfigurationException;
use Pimcore\Bundle\DataImporterBundle\Mapping\Operator\AbstractOperator;
use Pimcore\Bundle\DataImporterBundle\Mapping\Type\TransformationDataTypeService;
use Pimcore\Model\DataObject\SelectOptions\SurfaceType;
use \Pimcore\Model\DataObject\SelectOptions\AirportSurfaceType;
use ReflectionEnum;

class DecodeAirportSurfaceNotation extends AbstractOperator
{
    /**
     * The minimum value of similarity of matched surface name
     * @var integer
     */
    protected int $similarityThreshold;

    public function setSettings(array $settings): void
    {
        $this->similarityThreshold = $settings['similarityThreshold'] ?? 70;
    }

    /**
     * @param mixed $inputData
     * @param bool $dryRun
     *
     * @return array|array[]
     */
    public function process($inputData, bool $dryRun = false): array
    {
        //TODO: move these two lines to another transformers to get more atomic functionality
        $transformedInline = strtoupper($inputData);
        $transformedInline = preg_replace('/[^A-Z\-]+/im', '-', $transformedInline);

        $surfaceTypes = array_column(AirportSurfaceType::cases(), "value");
        $unprocessedSurfaces = explode('-', $transformedInline);
        $detectedSurfaces = [];
        $wrongSurfaces = false;
        $similarity = $this->similarityThreshold;
        $results = 0;
        $matches = [];

        foreach ($unprocessedSurfaces as $surfaceInput) {

            //we have exact match of surface, continue with another part of input
            if (in_array($surfaceInput, $surfaceTypes)) {
                $detectedSurfaces[] = $surfaceInput;
                continue;
            }

            //we are checking similarity factor
            foreach($surfaceTypes as $surfaceTypeToExam){
                if (
                    (!empty($surfaceInput) || strlen($surfaceInput) > 2)
                        && similar_text($surfaceInput, $surfaceTypeToExam, $similarity)
                        && $similarity > $this->similarityThreshold
                    ) {
                    $matches[] = $surfaceTypeToExam;
                }
            }

            //only one matched is acceptable, continue with another part of input
            if(count($matches) == 1){
                $detectedSurfaces[] = $matches[0];
                continue;
            }

            //we didn't match any surface type or too many, set wrongSurfaces flag that we found unmatchable surface
            $wrongSurfaces = true;
        }

        //as a final result
        //set up UNKNOWN option if some fields were not decoded properly, of course only if this option exists in select options
        if ($wrongSurfaces) {

            $rEnum = new ReflectionEnum(AirportSurfaceType::class);
            if ($rEnum->hasCase("UNKNOWN")) {
                $detectedSurfaces[] = AirportSurfaceType::UNKNOWN->value;
            }
        }

        return $detectedSurfaces;
    }

    /**
     * @param string $inputType
     * @param int|null $index
     *
     * @return string
     *
     * @throws InvalidConfigurationException
     */
    public
    function evaluateReturnType(string $inputType, int $index = null): string
    {
        if ($inputType != TransformationDataTypeService::DEFAULT_TYPE) {
            throw new InvalidConfigurationException(sprintf("Unsupported input type '%s' for airport surface decoder operator at transformation position %s", $inputType, $index));
        }

        return TransformationDataTypeService::DEFAULT_ARRAY;
    }
}
