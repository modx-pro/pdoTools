<?php
/* @var array $scriptProperties */
/* @var pdoFetch $pdoFetch */
if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {return false;}
$pdoFetch = new pdoFetch($modx, $scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

// Default variables
if (empty($tpl)) {$tpl = "@INLINE \n<url>\n\t<loc>[[+url]]</loc>\n\t<lastmod>[[+date]]</lastmod>\n\t<changefreq>[[+update]]</changefreq>\n\t<priority>[[+priority]]</priority>\n</url>";}
if (empty($tplWrapper)) {$tplWrapper = "@INLINE <?xml version=\"1.0\" encoding=\"[[++modx_charset]]\"?>\n<urlset xmlns=\"[[+schema]]\">\n[[+output]]\n</urlset>";}
if (empty($sitemapSchema)) {$sitemapSchema = 'http://www.sitemaps.org/schemas/sitemap/0.9';}
if (empty($outputSeparator)) {$outputSeparator = "\n";}

// Convert parameters from GoogleSiteMap if exists
if (!empty($itemTpl)) {$tpl = $itemTpl;}
if (!empty($containerTpl)) {$tplWrapper = $containerTpl;}
if (!empty($allowedtemplates)) {$templates = $allowedtemplates;}
if (!empty($maxDepth)) {$depth = $maxDepth;}
if (isset($hideDeleted)) {$showDeleted = !$hideDeleted;}
if (!empty($googleSchema)) {$sitemapSchema = $googleSchema;}
if (isset($published)) {$showUnpublished = !$published;}
if (isset($searchable)) {$showUnsearchable = !$searchable;}
if (!empty($excludeResources)) {
	$tmp = array_map('trim', explode(',', $excludeResources));
	foreach ($tmp as $v) {
		if (!empty($resources)) {
			$resources .= ',-'.$v;
		}
		else {
			$resources = '-'.$v;
		}
	}
}
if (!empty($excludeChildrenOf)) {
	$tmp = array_map('trim', explode(',', $excludeChildrenOf));
	foreach ($tmp as $v) {
		if (!empty($parents)) {
			$parents .= ',-'.$v;
		}
		else {
			$parents = '-'.$v;
		}
	}
}
if (!empty($startId)) {
	if (!empty($parents)) {
		$parents .= ','.$startId;
	}
	else {
		$parents = $startId;
	}
}
if (!empty($sortBy)) {$sortby = $sortBy;}
if (!empty($sortDir)) {$sortdir = $sortDir;}
if (!empty($priorityTV)) {
	if (!empty($scriptProperties['includeTVs'])) {
		$scriptProperties['includeTVs'] .= ','.$priorityTV;
	}
	else {
		$scriptProperties['includeTVs'] = $priorityTV;
	}
}
if (!empty($itemSeparator)) {$outputSeparator = $itemSeparator;}
//---


$class = 'modResource';
// Start building "Where" expression
$where = array();
if (empty($showUnpublished)) {$where['published'] = 1;}
if (empty($showHidden)) {
	//$where['hidemenu'] = 0;
	$where[] = "(`$class`.`hidemenu` = 0 OR `class_key` IN ('Ticket','Article'))";
}
if (empty($showDeleted)) {$where['deleted'] = 0;}
if (!empty($hideUnsearchable)) {$where['searchable'] = 1;}
if (empty($context)) {$context = $modx->context->key;}
$context = array_map('trim', explode(',', $context));
if (!empty($context) && is_array($context)) {
	$where['context_key:IN'] = $context;
}

// Filter by ids
if (!empty($resources)) {
	$resources = array_map('trim', explode(',', $resources));
	$resources_in = $resources_out = array();
	foreach ($resources as $v) {
		if (!is_numeric($v)) {continue;}
		if ($v < 0) {$resources_out[] = abs($v);}
		else {$resources_in[] = $v;}
	}
	if (!empty($resources_in)) {$where[$class.'.id:IN'] = $resources_in;}
	if (!empty($resources_out)) {$where[$class.'.id:NOT IN'] = $resources_out;}
}
// Filter by parents
if (!empty($parents)) {
	$pids = array();
	$parents = array_map('trim', explode(',', $parents));
	$parents_in = $parents_out = array();
	foreach ($parents as $v) {
		if (!is_numeric($v)) {continue;}
		if ($v < 0) {$parents_out[] = abs($v);}
		else {$parents_in[] = $v;}
	}
	if (!empty($depth) && $depth > 0) {
		$q = $modx->newQuery($class, array('id:IN' => array_merge($parents_in, $parents_out)));
		$q->select('id,context_key');
		if ($q->prepare() && $q->stmt->execute()) {
			while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
				$pids[$row['id']] = $row['context_key'];
			}
		}
		foreach ($pids as $k => $v) {
			if (!is_numeric($k)) {continue;}
			elseif (in_array($k, $parents_in)) {
				$parents_in = array_merge($parents_in, $modx->getChildIds($k, $depth, array('context' => $v)));
			}
			else {
				$parents_out = array_merge($parents_out, $modx->getChildIds($k, $depth, array('context' => $v)));
			}
		}
	}
	if (!empty($parents_in)) {$where[$class.'.parent:IN'] = $parents_in;}
	if (!empty($parents_out)) {$where[$class.'.parent:NOT IN'] = $parents_out;}
}
// Filter by templates
if (!empty($templates)) {
	$templates = array_map('trim', explode(',', $templates));
	$templates_in = $templates_out = array();
	foreach ($templates as $v) {
		if (!is_numeric($v)) {continue;}
		if ($v < 0) {$templates_out[] = abs($v);}
		else {$templates_in[] = $v;}
	}
	if (!empty($templates_in)) {$where[$class.'.template:IN'] = $templates_in;}
	if (!empty($templates_out)) {$where[$class.'.template:NOT IN'] = $templates_out;}
}

$select = array($class => 'id,editedon,createdon');

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
	'sortby' => $class.'.menuindex',
	'sortdir' => 'ASC',
	'return' => 'data',
	'limit' => 0,
	//'checkPermissions' => 'load',
	'fastMode' => true
);

// Merge all properties and run!
$pdoFetch->addTime('Query parameters ready');
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);
$rows = $pdoFetch->run();

$now = time();
$output = array();
foreach ($rows as $row) {
	$url = $modx->makeUrl($row['id'], '', '', 'full');

	$time = !empty($row['editedon'])
		? $row['editedon']
		: $row['createdon'];
	$date = date('Y-m-d', $time);

	$datediff = floor(($now - $time) / 86400);
	if ($datediff <= 1) {
		$priority = '1.0';
		$update = 'daily';
	} elseif (($datediff > 1) && ($datediff <= 7)) {
		$priority = '0.75';
		$update = 'weekly';
	} elseif (($datediff > 7) && ($datediff <= 30)) {
		$priority = '0.50';
		$update = 'weekly';
	} else {
		$priority = '0.25';
		$update = 'monthly';
	}

	if (!empty($priorityTV) && !empty($row[$priorityTV])) {
		$row['priority'] = $row[$priorityTV];
	}
	/* add item to output */
	$output[] = $pdoFetch->parseChunk($tpl,
		array(
			'url' => $url,
			'date' => $date,
			'update' => $update,
			'priority' => $priority,
		)
	);
}
$pdoFetch->addTime('Rows processed');

$output = implode($outputSeparator, $output);
$output = $pdoFetch->getChunk($tplWrapper, array(
	'schema' => $sitemapSchema,
	'output' => $output,
	'items' => $output
));
$pdoFetch->addTime('Rows wrapped');

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
	$output .= '<pre class="pdoResourcesLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

if (!empty($forceXML)) {
	header("Content-Type:text/xml");
	echo $output;
	exit();
}
else {
	return $output;
}