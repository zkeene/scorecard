<?php

function sqlSelectQuery(array $fields = array(), string $table, array $where = array(), array $orderby = array(), array $groupby = array()) {
    $fieldkeys = array_keys($fields);
    $lastfieldkey = array_pop($fieldkeys);
    $wherekeys = array_keys($where);
    $lastwherekey = array_pop($wherekeys);
    $orderbykeys = array_keys($orderby);
    $lastorderbykey = array_pop($orderbykeys);
    $groupbykeys = array_keys($groupby);
    $lastgroupbykey = array_pop($groupbykeys);

    $sql = 'select';

    if (!empty($fields)) {
        foreach ($fields as $key => $field) {
            $sql .= ' '.$field;
            if ($key != $lastfieldkey) {
                $sql .= ',';
            }
        }
    } else {
        $sql .= ' *';
    }

    $sql .= ' from '.$table;

    if (!empty($where)){
        $sql .= ' where';
        foreach ($where as $key1 => $whereitem) {
            $sql .= ' '.$whereitem;
            if ($key1 != $lastwherekey) {
                $sql .= ' and';
            }
        }
    }

    if (!empty($groupby)){
        $sql .= ' group by';
        foreach ($groupby as $key3 => $groupbyitem) {
            $sql .= ' '.$groupbyitem;
            if ($key3 != $lastgroupbykey) {
                $sql .= ',';
            }
        }
    }

    if (!empty($orderby)){
        $sql .= ' order by';
        foreach ($orderby as $key2 => $orderbyitem) {
            $sql .= ' '.$orderbyitem;
            if ($key2 != $lastorderbykey) {
                $sql .= ',';
            }
        }
    }

    global $conn;
    $result = $conn->query($sql);
    $results = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $results[]=$row;
        }
    }
    return $results;
}

function curr_format($amount) {
    return '$'.number_format($amount, 0);
}

function getQuarterFromDate ($date_to_check) {
    return ceil(date('n', strtotime($date_to_check))/ 3);
}

function day_diff ($date1, $date2) {
    return date_diff($date1, $date2) -> format("%r%a");
}
?>