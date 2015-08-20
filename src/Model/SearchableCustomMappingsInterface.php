<?php
namespace Brander\Bundle\EAVBundle\Model;

/**
 * not compatible with standard FOSElastica, igrvak only!
 * @author Tomfun <tomfun1990@gmail.com>
 */
interface SearchableCustomMappingsInterface extends SearchableEntityInterface
{
    const METHOD_NAME_MAPPINGS = 'getElasticaMappings';
    const ELASTICA_MAPPING_FULLTEXT_RU = 'my_full_text_analyzer_ru';
    const ELASTICA_MAPPING_FULLTEXT_FR = 'my_full_text_analyzer_fr';
    const ELASTICA_MAPPING_FULLTEXT_ES = 'my_full_text_analyzer_es';
    const ELASTICA_MAPPING_FULLTEXT_EN = 'my_full_text_analyzer_en';
    const ELASTICA_MAPPING_GEO_POINT = 'geo_point';
    const ELASTICA_MAPPING_NOT_ANALYZED = 'not_analyzed';

    /**
     * Customization elastica mappings. Used to set elastica analyzers on mapping field
     * @return array<string, string> elastica field -> analyzer name
     */
    public static function getElasticaMappings();
}