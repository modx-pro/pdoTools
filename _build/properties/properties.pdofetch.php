<?php
/**
 * Properties for the pdoTools snippet.
 *
 * @package pdotools
 * @subpackage build
 */
$properties = array(
	array(
		'name' => 'tpl'
		,'desc' => 'pdotools_tpl'
		,'type' => 'textfield'
		,'value' => ''
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'class'
		,'desc' => 'pdotools_class'
		,'type' => 'textfield'
		,'value' => 'modResource'
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'where '
		,'desc' => 'pdotools_where '
		,'type' => 'textarea'
		,'value' => '{"published":1,"deleted":0}'
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'leftJoin'
		,'desc' => 'pdotools_leftJoin'
		,'type' => 'textarea'
		,'value' => '{"modResource":{"alias":"Parent","on":"Parent.id=modResource.parent"},"modUser":{"alias":"User","on":"User.id=modResource.createdby"},"modUserProfile":{"alias":"Profile","on":"Profile.internalKey=modResource.createdby"}}'
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'innerJoin'
		,'desc' => 'pdotools_innerJoin'
		,'type' => 'textarea'
		,'value' => ''
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'rightJoin'
		,'desc' => 'pdotools_rightJoin'
		,'type' => 'textarea'
		,'value' => ''
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'select'
		,'desc' => 'pdotools_select'
		,'type' => 'textarea'
		,'value' => '{"modResource":"*","Parent":"Parent.pagetitle as parent_pagetitle, Parent.uri as parent_uri","User":"username","Profile":"fullname, email"}'
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'groupby'
		,'desc' => 'pdotools_groupby'
		,'type' => 'textfield'
		,'value' => ''
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'sortby'
		,'desc' => 'pdotools_sortby'
		,'type' => 'textfield'
		,'value' => ''
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'sortdir'
		,'desc' => 'pdotools_sortdir'
		,'type' => 'list'
		,'options' => array(
			array('text' => 'ASC','value' => 'ASC'),
			array('text' => 'DESC','value' => 'DESC'),
		)
		,'value' => 'DESC'
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'limit'
		,'desc' => 'pdotools_limit'
		,'type' => 'numberfield'
		,'value' => 10
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'offset'
		,'desc' => 'pdotools_offset'
		,'type' => 'numberfield'
		,'value' => 0
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'fastMode'
		,'desc' => 'pdotools_fastMode'
		,'type' => 'combo-boolean'
		,'value' => 'false'
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'return'
		,'desc' => 'pdotools_return'
		,'type' => 'list'
		,'options' => array(
			array('text' => 'Chunks','value' => 'chunks')
			,array('text' => 'Data','value' => 'data')
			,array('text' => 'SQL','value' => 'sql')
		)
		,'value' => 'chunks'
		,'lexicon' => 'pdotools:properties'
	)
	,array(
		'name' => 'totalVar '
		,'desc' => 'pdotools_totalVar'
		,'type' => 'textfield'
		,'value' => 'total'
		,'lexicon' => 'pdotools:properties'
	)
/*
	,array(
		'name' => ''
		,'desc' => 'pdotools_'
		,'type' => 'textfield'
		,'value' => ''
		,'lexicon' => 'pdotools:properties'
	)
*/
);

return $properties;