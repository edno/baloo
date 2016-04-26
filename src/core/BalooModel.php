<?php

namespace Baloo;

/*
 * class BalooModel
 * Static class that contains the data model: tables and relations
 *
 * @package baloo
 */

final class BalooModel
{
    use Singleton;

    private static $table = array(
        'datasource' => 'datasource',
        'datasourcetype' => 'datasourcetype',
        'entitytype' => 'entitytype',
        'entityfield' => 'entityfield',
        'entityfieldinfo' => 'entityfieldtype',
        'entityobject' => 'dataobject',
        'entityobjectvalue' => 'datavalue',
        'entityobjectblob' => 'datablob',
        'entityobjectfile' => 'datafile',
    );

    public static function __callStatic($name, $arg)
    {
        $matches = array();
        if (preg_match('/^([a-z]+)([A-Z]+[A-z_]*)$/', $name, $matches) !== 1) {
            throw new BalooException('Invalid Static Call "'.$name.'" in '.get_class($this));
        }
        $var = $matches[1];
        if (isset($matches[2])) {
            $key = strtolower($matches[2]);
            return static::${$var}[$key];
        } else {
            return static::${$var};
        }
    }
}
