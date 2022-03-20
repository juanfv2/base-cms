<?php

function _file_delimiter($file, $checkLines = 2)
{
    $file = new \SplFileObject($file);
    $delimiters = [',', '\t', ';', '|', ':'];
    $results = [];
    $i = 0;
    while ($file->valid() && $i <= $checkLines) {
        $line = $file->fgets();
        // logger(__FILE__ . ':' . __LINE__ . ' $line ', [$line]);
        foreach ($delimiters as $delimiter) {
            $regExp = '/[' . $delimiter . ']/';
            $fields = preg_split($regExp, $line);
            if (count($fields) > 1) {
                if (!empty($results[$delimiter])) {
                    $results[$delimiter]++;
                } else {
                    $results[$delimiter] = 1;
                }
            }
        }
        $i++;
    }

    if (count($results) > 0) {
        $results = array_keys($results, max($results));
        return $results[0];
    }

    return ',';
}

function _array_combine($keys, $values)
{
    $result = [];
    foreach ($keys as $i => $k) {
        $result[$k] = isset($values[$i]) ? $values[$i] : '';
    }

    return $result;
}


/**
 *
 * Remove characters that are illegal or don't make for good mysql names.
 *
 * @return string $name, with certain characters removed
 *
 * @param string $name
 */
function _sanitize($name)
{
    $name = Str::of($name)->lower()->snake()->ascii();
    return str_replace([':', "'", "/", '\\', ".", '"', '?', '$', '-', '*', '`', '+', ','], '_', $name);
}
