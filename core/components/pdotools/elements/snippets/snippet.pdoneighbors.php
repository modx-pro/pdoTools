<?php
/* @var array $scriptProperties */
/* @var pdoFetch $pdoFetch */
if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {return false;}
$pdoFetch = new pdoFetch($modx, $scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

if (empty($id)) {$id = $modx->resource->id;}
if (empty($limit)) {$limit = 1;}
if (empty($tplWrapper)) {$tplWrapper = '@INLINE <div class="neighbors">[[+prev]][[+up]][[+next]]</div>';}
if (!isset($tplPrev)) {$tplPrev = '@INLINE <span class="link-prev">&larr; <a href="/[[+uri]]">[[+pagetitle]]</a></span>';}
if (!isset($tplUp)) {$tplUp = '@INLINE <span class="link-up">&uarr; <a href="/[[+uri]]">[[+pagetitle]]</a></span>';}
if (!isset($tplNext)) {$tplNext = '@INLINE <span class="link-next"><a href="/[[+uri]]">[[+pagetitle]]</a> &rarr;</span>';}
if (!isset($outputSeparator)) {$outputSeparator = "\n";}
$fastMode = !empty($fastMode);

$class = 'modResource';
$resource = ($id == $modx->resource->id)
	? $modx->resource
	: $modx->getObject($class, $id);
if (!$resource) {return '';}

$where = array("($class.parent = $resource->parent OR $class.id = $resource->parent)");
$where[$class.'.context_key'] = $resource->context_key;
if (empty($showUnpublished)) {$where[$class.'.published'] = 1;}
if (empty($showHidden)) {$where[$class.'.hidemenu'] = 0;}
if (empty($showDeleted)) {$where[$class.'.deleted'] = 0;}
if (!empty($hideContainers)) {$where[$class.'.isfolder'] = 0;}

// Fields to select
$resourceColumns = array_keys($modx->getFieldMeta($class));
if (empty($includeContent)) {
	$key = array_search('content', $resourceColumns);
	unset($resourceColumns[$key]);
}
$select = array($class => implode(',',$resourceColumns));

// Add custom parameters
foreach (array('where','select') as $v) {
	if (!empty($scriptProperties[$v])) {
		$tmp = $modx->fromJSON($scriptProperties[$v]);
		if (is_array($tmp)) {
			$$v = array_merge($$v, $tmp);
		}
	}
	unset($scriptProperties[$v]);
}
$pdoFetch->addTime('Conditions prepared');

// Default parameters
$default = array(
	'class' => $class,
	'where' => $modx->toJSON($where),
	'select' => $modx->toJSON($select),
	//'groupby' => $class.'.id',
	'sortby' => $class.'.menuindex',
	'sortdir' => 'ASC',
	'return' => 'data',
	'limit' => 0,
	'totalVar' => 'neighbors.total',
);

// Merge all properties and run!
unset($scriptProperties['limit']);
$pdoFetch->addTime('Query parameters ready');
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);

$rows = $pdoFetch->run();
$output = array(
	'prev' => array(),
	'up' => '',
	'next' => array(),
);

$found = false;
$prev = $next = array();
if (!empty($rows)) {
	foreach ($rows as $row) {
		if ($row['id'] == $resource->id) {
			$found = true;
			continue;
		}
		elseif ($row['id'] == $resource->parent) {
			$output['up'] = $pdoFetch->getChunk($tplUp, $row, $fastMode);
		}
		elseif ($found) {
			$next[] = $row;
		}
		else {
			$prev[] = $row;
		}
	}
}

while (count($output['prev']) < $limit && !empty($prev)) {
	$output['prev'][] = $pdoFetch->getChunk($tplPrev, array_pop($prev), $fastMode);
}
while (count($output['next']) < $limit && !empty($next)) {
	$output['next'][] = $pdoFetch->getChunk($tplNext, array_shift($next), $fastMode);
}
$pdoFetch->addTime('Chunks processed');

$log = '';
if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
	$log .= '<pre class="pdoNeighborsLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}
$output['prev'] = implode($outputSeparator, $output['prev']);
$output['next'] = implode($outputSeparator, $output['next']);

if (!empty($toSeparatePlaceholders)) {
	$output['log'] = $log;
	$modx->setPlaceholders($output, $toSeparatePlaceholders);
}
else {
	$output = $pdoFetch->getChunk($tplWrapper, $output, $fastMode);

	$output .= $log;
	if (!empty($toPlaceholder)) {
		$modx->setPlaceholder($toPlaceholder, $output);
	}
	else {
		return $output;
	}
}