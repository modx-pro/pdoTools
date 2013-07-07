<?php
/* @var array $scriptProperties */
/* @var pdoFetch $pdoFetch */
$pdoFetch = $modx->getService('pdofetch','pdoFetch', MODX_CORE_PATH.'components/pdotools/model/pdotools/',$scriptProperties);
$pdoFetch->addTime('pdoTools loaded.');

$class = 'modResource';
$returnIds = !empty($scriptProperties['returnIds']);

// Start building "Where" expression
$where = array();
if (empty($showUnpublished)) {$where['published'] = 1;}
if (empty($showHidden)) {$where['hidemenu'] = 0;}
if (empty($showDeleted)) {$where['deleted'] = 0;}
if (!empty($hideContainers)) {$where['isfolder'] = 0;}
if (!empty($context)) {$where['context_key'] = trim($context);}

// Filter by ids
if (!empty($resources)){
	$resources = array_map('trim', explode(',', $resources));
	$in = $out = array();
	foreach ($resources as $v) {
		if (!is_numeric($v)) {continue;}
		if ($v < 0) {$out[] = abs($v);}
		else {$in[] = $v;}
	}
	if (!empty($in)) {$where['id:IN'] = $in;}
	if (!empty($out)) {$where['id:NOT IN'] = $out;}
}
// Filter by parents
if (empty($parents) && $parents != '0') {$parents = $modx->resource->id;}
if (!empty($parents) && $parents > 0) {
	$pids = array_map('trim', explode(',', $parents));
	$parents = $pids;
	if (!empty($depth) && $depth > 0) {
		foreach ($pids as $v) {
			if (!is_numeric($v)) {continue;}
			$parents = array_merge($parents, $modx->getChildIds($v, $depth));
		}
	}

	$where['parent:IN'] = $parents;
}

// Adding custom where parameters
if (!empty($scriptProperties['where'])) {
	$tmp = $modx->fromJSON($scriptProperties['where']);
	if (is_array($tmp)) {
		$where = array_merge($where, $tmp);
	}
}
unset($scriptProperties['where']);
$pdoFetch->addTime('"Where" expression built.');
// End of building "Where" expression

// Fields to select
if ($returnIds) {
	$resourceColumns = "`$class`.`id`";
}
else {
	$resourceColumns = !empty($includeContent) ?  $modx->getSelectColumns($class, $class) : $modx->getSelectColumns($class, $class, '', array('content'), true);
}
$select = array('"'.$class.'":"'.$resourceColumns.'"');

// Default parameters
$default = array(
	'class' => $class
	,'where' => $modx->toJSON($where)
	,'select' => '{'.implode(',',$select).'}'
	,'sortby' => $class.'.id'
	,'sortdir' => 'DESC'
	,'groupby' => $class.'.id'
	,'fastMode' => false
	,'return' => 'data'
	,'nestedChunkPrefix' => 'pdotools_'
);

if ($returnIds) {
	unset($scriptProperties['includeTVs'], $default['groupby']);
}
if (!empty($in) && (empty($scriptProperties['sortby']) || $scriptProperties['sortby'] == 'id')) {
	$scriptProperties['sortby'] = "find_in_set(`$class`.`id`,'".implode(',', $in)."')";
	$scriptProperties['sortdir'] = '';
}

// Merge all properties and run!
$pdoFetch->addTime('Query parameters are prepared.');
$pdoFetch->setConfig(array_merge($default, $scriptProperties));
$rows = $pdoFetch->run();

// Processing rows
$output = null;
if ($returnIds) {
	$ids = array();
	foreach ($rows as $row) {
		$ids[] = $row['id'];
	}
	$output = implode(',', $ids);
}
else if (!empty($rows) && is_array($rows)) {
	$offset = !empty($offset) ? (integer) $offset : 0;
	$idx = !empty($idx) && $idx !== '0' ? (integer) $idx : 1;
	$first = empty($first) && $first !== '0' ? 1 : (integer) $first;
	$last = empty($last) ? (count($rows) + $idx - 1) : (integer) $last;

	foreach ($rows as $k => $row) {
		$row['idx'] = $idx + $offset;
		// Processing chunk
		$tpl = $pdoFetch->defineChunk($idx, $first, $last);
		$output[] = empty($tpl)
			? '<pre>'.$pdoFetch->getChunk('', $row).'</pre>'
			: $pdoFetch->getChunk($tpl, $row, $pdoFetch->config['fastMode']);
		$idx++;
	}

	$pdoFetch->addTime('Returning processed chunks');
	if (empty($outputSeparator)) {$outputSeparator = "\n";}
	if (!empty($output)) {
		$output = implode($outputSeparator, $output);
	}
}

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
	$output .= '<pre class="pdoResourcesLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

// Return output
if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
	$output = $pdoFetch->getChunk($tplWrapper, array('output' => $output), $pdoFetch->config['fastMode']);
}

if (!empty($toPlaceholder)) {
	$modx->setPlaceholder($toPlaceholder, $output);
}
else {
	return $output;
}