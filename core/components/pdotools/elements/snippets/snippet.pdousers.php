<?php
/** @var array $scriptProperties */
/** @var pdoFetch $pdoFetch */
$fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
$path = $modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/', true);
if ($pdoClass = $modx->loadClass($fqn, $path, false, true)) {
    $pdoFetch = new $pdoClass($modx, $scriptProperties);
} else {
    return false;
}
$pdoFetch->addTime('pdoTools loaded');

$class = 'modUser';
$profile = 'modUserProfile';
$member = 'modUserGroupMember';

// Start building "Where" expression
$where = array();
if (empty($showInactive)) {
    $where[$class . '.active'] = 1;
}
if (empty($showBlocked)) {
    $where[$profile . '.blocked'] = 0;
}

// Add users profiles and groups
$innerJoin = array(
    $profile => array('alias' => $profile, 'on' => "$class.id = $profile.internalKey"),
);

// Filter by users, groups and roles
$tmp = array(
    'users' => array(
        'class' => $class,
        'name' => 'username',
        'join' => $class . '.id',
    ),
    'groups' => array(
        'class' => 'modUserGroup',
        'name' => 'name',
        'join' => $member . '.user_group',
    ),
    'roles' => array(
        'class' => 'modUserGroupRole',
        'name' => 'name',
        'join' => $member . '.role',
    ),
);
foreach ($tmp as $k => $p) {
    if (!empty($$k)) {
        $$k = array_map('trim', explode(',', $$k));
        ${$k . '_in'} = ${$k . '_out'} = $fetch_in = $fetch_out = array();
        foreach ($$k as $v) {
            if (is_numeric($v)) {
                if ($v[0] == '-') {
                    ${$k . '_out'}[] = abs($v);
                } else {
                    ${$k . '_in'}[] = abs($v);
                }
            } else {
                if ($v[0] == '-') {
                    $fetch_out[] = $v;
                } else {
                    $fetch_in[] = $v;
                }
            }
        }

        if (!empty($fetch_in) || !empty($fetch_out)) {
            $q = $modx->newQuery($p['class'], array($p['name'] . ':IN' => array_merge($fetch_in, $fetch_out)));
            $q->select('id,' . $p['name']);
            $tstart = microtime(true);
            if ($q->prepare() && $q->stmt->execute()) {
                $modx->queryTime += microtime(true) - $tstart;
                $modx->executedQueries++;
                while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (in_array($row[$p['name']], $fetch_in)) {
                        ${$k . '_in'}[] = $row['id'];
                    } else {
                        ${$k . '_out'}[] = $row['id'];
                    }
                }
            }
        }

        if (!empty(${$k . '_in'})) {
            $where[$p['join'] . ':IN'] = ${$k . '_in'};
        }
        if (!empty(${$k . '_out'})) {
            $where[$p['join'] . ':NOT IN'] = ${$k . '_out'};
        }
    }
}

if (!empty($groups_in) || !empty($groups_out) || !empty($roles_in) || !empty($roles_out)) {
    $innerJoin[$member] = array('alias' => $member, 'on' => "$class.id = $member.member");
}

// Fields to select
$select = array(
    $profile => implode(',', array_keys($modx->getFieldMeta($profile))),
    $class => implode(',', array_keys($modx->getFieldMeta($class))),
);

// Add custom parameters
foreach (array('where', 'innerJoin', 'select') as $v) {
    if (!empty($scriptProperties[$v])) {
        $tmp = $scriptProperties[$v];
        if (!is_array($tmp)) {
            $tmp = json_decode($tmp, true);
        }
        if (is_array($tmp)) {
            $$v = array_merge($$v, $tmp);
        }
    }
    unset($scriptProperties[$v]);
}
$pdoFetch->addTime('Conditions prepared');

$default = array(
    'class' => $class,
    'innerJoin' => json_encode($innerJoin),
    'where' => json_encode($where),
    'select' => json_encode($select),
    'groupby' => $class . '.id',
    'sortby' => $class . '.id',
    'sortdir' => 'ASC',
    'fastMode' => false,
    'return' => !empty($returnIds) ? 'ids' : 'chunks',
    'disableConditions' => true,
);

if (!empty($users_in) && (empty($scriptProperties['sortby']) || $scriptProperties['sortby'] == $class . '.id')) {
    $scriptProperties['sortby'] = "find_in_set(`$class`.`id`,'" . implode(',', $users_in) . "')";
    $scriptProperties['sortdir'] = '';
}

// Merge all properties and run!
$pdoFetch->addTime('Query parameters ready');
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);
$output = $pdoFetch->run();

$log = '';
if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
    $log .= '<pre class="pdoUsersLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

// Return output
if (!empty($returnIds)) {
    $modx->setPlaceholder('pdoUsers.log', $log);
    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $output);
    } else {
        return $output;
    }
} elseif (!empty($toSeparatePlaceholders)) {
    $output['log'] = $log;
    $modx->setPlaceholders($output, $toSeparatePlaceholders);
} else {
    $output .= $log;

    if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
        $output = $pdoFetch->getChunk($tplWrapper, array('output' => $output), $pdoFetch->config['fastMode']);
    }

    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $output);
    } else {
        return $output;
    }
}