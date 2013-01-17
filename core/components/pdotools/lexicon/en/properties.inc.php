<?php
/**
 * Properties English Lexicon Entries for pdoTools
 *
 * @package pdotools
 * @subpackage lexicon
 */
$_lang['pdotools_tpl'] = 'Chunk for templating results. If empty - will be printed array of results.';
$_lang['pdotools_class'] = 'Base class for construct of query. By default, modResource';
$_lang['pdotools_where'] = 'JSON encoded array with "where" condition.';
$_lang['pdotools_leftJoin'] = 'JSON encoded arrays with leftJoin conditions. Used keys "class", "alias" and "on".';
$_lang['pdotools_innerJoin'] = 'JSON encoded arrays with innerJoin conditions. Used keys "class", "alias" and "on".';
$_lang['pdotools_rightJoin'] = 'JSON encoded arrays with rightJoin conditions. Used keys "class", "alias" and "on".';
$_lang['pdotools_select'] = 'Json encoded array with "select" condition. You can use "all" or "*" for selection of all fields of class.';
$_lang['pdotools_groupby'] = 'Field for grouping query.';
$_lang['pdotools_sortby'] = 'Query sort by.';
$_lang['pdotools_sortdir'] = 'Direction of sort.';
$_lang['pdotools_limit'] = 'Query limit.';
$_lang['pdotools_offset'] = 'Query offset.';
$_lang['pdotools_fastMode'] = 'Fast chunks processing. If true, MODX parser will not be used and unprocessed tags will be cut.';
$_lang['pdotools_return'] = 'How to return result? Raw SQL, array with results or processed chunks.';
$_lang['pdotools_totalVar'] = 'Name of placeholder for setting total count of rows.';