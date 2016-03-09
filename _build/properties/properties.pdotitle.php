<?php

$properties = array();

$tmp = array(

    'id' => array(
        'type' => 'numberfield',
        'value' => 0,
    ),
    'exclude' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'limit' => array(
        'type' => 'numberfield',
        'value' => 3,
        'desc' => 'pdotools_prop_title_limit',
    ),
    'titleField' => array(
        'type' => 'textfield',
        'value' => 'longtitle',
    ),

    'cache' => array(
        'type' => 'numberfield',
        'value' => false,
        'desc' => 'pdotools_prop_title_cache',
    ),
    'cacheTime' => array(
        'type' => 'numberfield',
        'value' => 0,
    ),

    'tplPages' => array(
        'type' => 'textfield',
        'value' => '@INLINE [[%pdopage_page]] [[+page]] [[%pdopage_from]] [[+pageCount]]',
    ),
    'pageVarKey' => array(
        'type' => 'textfield',
        'value' => 'page',
    ),

    'tplSearch' => array(
        'type' => 'textfield',
        'value' => '@INLINE «[[+mse2_query]]»',
    ),
    'queryVarKey' => array(
        'type' => 'textfield',
        'value' => 'query',
    ),
    'minQuery' => array(
        'type' => 'numberfield',
        'value' => 3,
    ),

    'outputSeparator' => array(
        'type' => 'textfield',
        'value' => ' / ',
        'desc' => 'pdotools_prop_title_outputSeparator',
    ),

    'registerJs' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
);

foreach ($tmp as $k => $v) {
    $properties[] = array_merge(array(
        'name' => $k,
        'desc' => 'pdotools_prop_' . $k,
        'lexicon' => 'pdotools:properties',
    ), $v);
}

return $properties;
