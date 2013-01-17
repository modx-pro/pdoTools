<?php
/**
 * Properties for the pdoTools snippet.
 *
 * @package pdotools
 * @subpackage build
 */
$properties = array(
	array(
		'name' => 'tpl',
		'desc' => 'prop_pdotools.tpl_desc',
		'type' => 'textfield',
		'options' => '',
		'value' => 'tpl.pdoTools.item',
		'lexicon' => 'pdotools:properties',
	),
	array(
		'name' => 'sortBy',
		'desc' => 'prop_pdotools.sortby_desc',
		'type' => 'textfield',
		'options' => '',
		'value' => 'name',
		'lexicon' => 'pdotools:properties',
	),
	array(
		'name' => 'sortDir',
		'desc' => 'prop_pdotools.sortdir_desc',
		'type' => 'list',
		'options' => array(
			array('text' => 'ASC','value' => 'ASC'),
			array('text' => 'DESC','value' => 'DESC'),
		),
		'value' => 'ASC',
		'lexicon' => 'pdotools:properties',
	),
	array(
		'name' => 'limit',
		'desc' => 'prop_pdotools.limit_desc',
		'type' => 'numberfield',
		'options' => '',
		'value' => 5,
		'lexicon' => 'pdotools:properties',
	),
	array(
		'name' => 'outputSeparator',
		'desc' => 'prop_pdotools.outputseparator_desc',
		'type' => 'textfield',
		'options' => '',
		'value' => '',
		'lexicon' => 'pdotools:properties',
	),
	array(
		'name' => 'toPlaceholder',
		'desc' => 'prop_pdotools.toplaceholder_desc',
		'type' => 'combo-boolean',
		'options' => '',
		'value' => false,
		'lexicon' => 'pdotools:properties',
	),
/*
	array(
		'name' => '',
		'desc' => 'prop_pdotools.',
		'type' => 'textfield',
		'options' => '',
		'value' => '',
		'lexicon' => 'pdotools:properties',
	),
	*/
);

return $properties;