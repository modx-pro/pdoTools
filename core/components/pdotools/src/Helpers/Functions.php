<?php

namespace MODX\Components\PDOTools\Helpers;

class Functions
{

    /**
     * Builds a hierarchical tree from given array
     *
     * @param array $tmp Array with rows
     * @param string $id Name of primary key
     * @param string $parent Name of parent key
     * @param array $roots Allowed roots of nodes
     *
     * @return array
     */
    public static function buildTree($tmp = [], $id = 'id', $parent = 'parent', array $roots = [])
    {
        if (empty($id)) {
            $id = 'id';
        }
        if (empty($parent)) {
            $parent = 'parent';
        }

        if (count($tmp) == 1) {
            $row = current($tmp);
            $tree = [
                $row[$parent] => [
                    'children' => [
                        $row[$id] => $row,
                    ],
                ],
            ];
        } else {
            $rows = $tree = [];
            foreach ($tmp as $v) {
                $rows[$v[$id]] = $v;
            }

            foreach ($rows as $id => &$row) {
                if (empty($row[$parent]) || (!isset($rows[$row[$parent]]) && in_array($id, $roots))) {
                    $tree[$id] = &$row;
                } else {
                    $rows[$row[$parent]]['children'][$id] = &$row;
                }
            }
        }

        return $tree;
    }
}