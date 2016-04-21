<?php
$modx->lexicon->load('pdotools:pdoarchive');

/** @var array $scriptProperties */
$tplWrapper = $modx->getOption('tplWrapper', $scriptProperties);
$tplYear = $modx->getOption('tplYear', $scriptProperties);
$tplMonth = $modx->getOption('tplMonth', $scriptProperties);
$tplDay = $modx->getOption('tplDay', $scriptProperties);
$tpl = $modx->getOption('tpl', $scriptProperties);
$dateField = $modx->getOption('dateField', $scriptProperties, 'createdon', true);
$dateFormat = $modx->getOption('dateFormat', $scriptProperties, 'H:i', true);
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, "\n");

// Adding extra parameters into special place so we can put them in a results
/** @var modSnippet $snippet */
$additionalPlaceholders = $properties = array();
if (isset($this) && $this instanceof modSnippet) {
    $properties = $this->get('properties');
} elseif ($snippet = $modx->getObject('modSnippet', array('name' => 'pdoResources'))) {
    $properties = $snippet->get('properties');
}
if (!empty($properties)) {
    foreach ($scriptProperties as $k => $v) {
        if (!isset($properties[$k])) {
            $additionalPlaceholders[$k] = $v;
        }
    }
}
$scriptProperties['additionalPlaceholders'] = $additionalPlaceholders;
if (isset($parents) && $parents === '') {
    $scriptProperties['parents'] = $modx->resource->id;
}
$scriptProperties['return'] = 'data';
/** @var pdoFetch $pdoFetch */
$fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
$path = $modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/', true);
if ($pdoClass = $modx->loadClass($fqn, $path, false, true)) {
    $pdoFetch = new $pdoClass($modx, $scriptProperties);
} else {
    return false;
}
$pdoFetch->addTime('pdoTools loaded');
$rows = $pdoFetch->run();

// Process rows
$tree = array();
foreach ($rows as $row) {
    $year = date('Y', $row[$dateField]);
    $month = date('m', $row[$dateField]);
    $day = date('d', $row[$dateField]);
    $tree[$year][$month][$day][] = $row;
}

$output = '';
foreach ($tree as $year => $months) {
    $rows_year = '';
    $count_year = 0;

    foreach ($months as $month => $days) {
        $rows_month = '';
        $count_month = 0;

        foreach ($days as $day => $resources) {
            $rows_day = array();
            $count_day = 0;
            $idx = 1;

            foreach ($resources as $resource) {
                $resource['day'] = $day;
                $resource['month'] = $month;
                $resource['year'] = $year;
                $resource['date'] = strftime($dateFormat, $resource[$dateField]);
                $resource['idx'] = $idx++;
                $resource['menutitle'] = !empty($resource['menutitle'])
                    ? $resource['menutitle']
                    : $resource['pagetitle'];
                // Add placeholder [[+link]] if specified
                if (!empty($scriptProperties['useWeblinkUrl'])) {
                    if (!isset($resource['context_key'])) {
                        $resource['context_key'] = '';
                    }
                    if (isset($resource['class_key']) && ($resource['class_key'] == 'modWebLink')) {
                        $resource['link'] = isset($resource['content']) && is_numeric(trim($resource['content'], '[]~ '))
                            ? $pdoFetch->makeUrl(intval(trim($resource['content'], '[]~ ')), $resource)
                            : (isset($resource['content']) ? $resource['content'] : '');
                    } else {
                        $resource['link'] = $pdoFetch->makeUrl($resource['id'], $resource);
                    }
                } else {
                    $resource['link'] = '';
                }
                $tpl = $pdoFetch->defineChunk($resource);
                $rows_day[] = $pdoFetch->getChunk($tpl, $resource);
                $count_year++;
                $count_month++;
                $count_day++;
            }

            $rows_month .= !empty($tplDay)
                ? $pdoFetch->getChunk($tplDay, array(
                    'day' => $day,
                    'month' => $month,
                    'year' => $year,
                    'count' => $count_day,
                    'wrapper' => implode($outputSeparator, $rows_day),
                ), $pdoFetch->config['fastMode'])
                : implode($outputSeparator, $rows_day);
        }

        $rows_year .= !empty($tplMonth)
            ? $pdoFetch->getChunk($tplMonth, array(
                'month' => $month,
                'month_name' => $modx->lexicon('pdoarchive_month_' . $month),
                'year' => $year,
                'count' => $count_month,
                'wrapper' => $rows_month,
            ), $pdoFetch->config['fastMode'])
            : $rows_month;
    }

    $output .= !empty($tplYear)
        ? $pdoFetch->getChunk($tplYear, array(
            'year' => $year,
            'count' => $count_year,
            'wrapper' => $rows_year,
        ), $pdoFetch->config['fastMode'])
        : $rows_year;
}
$pdoFetch->addTime('Rows processed');

// Return output
if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
    $output = $pdoFetch->getChunk(
        $tplWrapper,
        array_merge($additionalPlaceholders, array('output' => $output)),
        $pdoFetch->config['fastMode']
    );
    $pdoFetch->addTime('Rows wrapped');
}

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
    $output .= '<pre class="pdoArchiveLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
} else {
    return $output;
}
