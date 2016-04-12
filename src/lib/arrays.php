<?php

namespace Baloo\Lib\Arrays;

function array_diff_assoc_recursive($array1, $array2)
{
    $difference = array();
    foreach ($array1 as $key => $value) {
        if (is_array($value)) {
            if (!isset($array2[$key]) || !is_array($array2[$key])) {
                $difference[$key] = $value;
            } else {
                $new_diff = array_diff_assoc_recursive($value, $array2[$key]);
                if (!empty($new_diff)) {
                    $difference[$key] = $new_diff;
                }
            }
        } elseif (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
            $difference[$key] = $value;
        }
    }

    return $difference;
}

function array_unique_recursive($array)
{
    $result = array_map('unserialize', array_unique(array_map('serialize', $array)));
    foreach ($result as $key => $value) {
        if (is_array($value)) {
            $result[$key] = array_unique_recursive($value);
        }
    }

    return $result;
}

function array_diff_recursive($array1, $array2)
{
    $difference = array();
    $array1 = array_values($array1); // clean keys
    $array2 = array_values($array2); // clean keys
    foreach ($array1 as $value) {
        if (in_multiarray($value, $array2) === false) {
            $difference[] = $value;
        }
    }

    return $difference;
}

function in_multiarray($elem, $array)
{
    foreach ($array as $item) {
        if ($item == $elem) {
            return true;
        } elseif (is_array($item)) {
            if (sizeof(array_diff($item, $elem)) == 0) {
                return true;
            } elseif (in_multiarray($elem, $item)) {
                return true;
            }
        }
    }

    return false;
}
