<?php

use ModxPro\PdoTools\Fetch;
use MODX\Revolution\modUser;
use MODX\Revolution\modUserGroup;
use MODX\Revolution\modUserGroupMember;
use MODX\Revolution\modUserGroupRole;
use MODX\Revolution\modUserProfile;

/** @var array $scriptProperties */
/** @var \MODX\Revolution\modX $modx */

if (!empty($returnIds)) {
    $scriptProperties['return'] = $return = 'ids';
} elseif (!isset($return)) {
    $scriptProperties['return'] = $return = 'chunks';
}
$modx->services['pdotools_config'] = $scriptProperties;
$pdoFetch = $modx->services->get(Fetch::class);
$pdoFetch->addTime('pdoTools loaded');

$class = modUser::class;
$alias_class = $modx->getAlias($class);
$profile = modUserProfile::class;
$alias_profile = $modx->getAlias($profile);
$member = modUserGroupMember::class;
$alias_member = $modx->getAlias($member);

// Start building "Where" expression
$where = [];
if (empty($showInactive)) {
    $where[$alias_class . '.active'] = 1;
}
if (empty($showBlocked)) {
    $where[$alias_profile . '.blocked'] = 0;
}

// Add users profiles and groups
$innerJoin = [
    $profile => ['class' => $profile, 'alias' => $alias_profile, 'on' => "$alias_class.id = $alias_profile.internalKey"],
];

// Filter by users, groups and roles
$tmp = [
    'users' => [
        'class' => $class,
        'name' => 'username',
        'join' => $alias_class . '.id',
    ],
    'groups' => [
        'class' => modUserGroup::class,
        'name' => 'name',
        'join' => $alias_member . '.user_group',
    ],
    'roles' => [
        'class' => modUserGroupRole::class,
        'name' => 'name',
        'join' => $alias_member . '.role',
    ],
];
foreach ($tmp as $k => $p) {
    if (!empty($$k)) {
        $$k = array_map('trim', explode(',', $$k));
        ${$k . '_in'} = ${$k . '_out'} = $fetch_in = $fetch_out = [];
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
            $q = $modx->newQuery($p['class'], [$p['name'] . ':IN' => array_merge($fetch_in, $fetch_out)]);
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
    $innerJoin[$alias_member] = ['class' => $member, 'alias' => $alias_member, 'on' => "$alias_class.id = $alias_member.member"];
}

// Fields to select
$select = [
    $profile => implode(',', array_diff(array_keys($modx->getFieldMeta($profile)), ['sessionid'])),
    $class => implode(',', array_diff(array_keys($modx->getFieldMeta($class)), ['password', 'cachepwd', 'salt', 'session_stale', 'remote_key', 'remote_data', 'hash_class'])),
];

// Add custom parameters
foreach (['where', 'innerJoin', 'select'] as $v) {
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

$default = [
    'class' => $class,
    'innerJoin' => $innerJoin,
    'where' => $where,
    'select' => $select,
    'groupby' => $alias_class . '.id',
    'sortby' => $alias_class . '.id',
    'sortdir' => 'ASC',
    'fastMode' => false,
    'return' => $return,
    'disableConditions' => true,
];

if (!empty($users_in) && (empty($scriptProperties['sortby']) || $scriptProperties['sortby'] == $alias_class . '.id')) {
    $scriptProperties['sortby'] = "find_in_set(`$alias_class`.`id`,'" . implode(',', $users_in) . "')";
    $scriptProperties['sortdir'] = '';
}

// Merge all properties and run!
$pdoFetch->addTime('Query parameters ready');
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);
$output = $pdoFetch->run();

$log = '';
if ($modx->user->isAuthenticated('mgr') && !empty($showLog)) {
    $modx->setPlaceholder('pdoUsersLog',  print_r($pdoFetch->getTime(), true));
}

// Return output
if (!empty($returnIds)) {
    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $output);
    } else {
        return $output;
    }
} elseif ($return === 'data') {
    return $output;
} elseif (!empty($toSeparatePlaceholders)) {
    $modx->setPlaceholders($output, $toSeparatePlaceholders);
} else {
    if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
        $output = $pdoFetch->getChunk($tplWrapper, ['output' => $output], $pdoFetch->config['fastMode']);
    }

    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $output);
    } else {
        return $output;
    }
}