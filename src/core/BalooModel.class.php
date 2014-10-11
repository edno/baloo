<?php

/**
 * class BalooModel
 * Static class that contains the data model: tables and relations
 *
 * @package baloo
 */
final class BalooModel {

	private static $table = array(
        'datasource' 		=> 'datasource',
		'datasourcetype' 	=> 'datasourcetype',
		'entitytype' 		=> 'entitytype',
		'entityfield'		=> 'entityfield',
		'entityfieldtype'	=> 'entityfieldtype',
		'entityobject'		=> 'dataobject',
		'entityobjectvalue'	=> 'datavalue',
		
	);
	
	// Make magic method as protected
	protected function __construct(){}
	protected function __clone(){}
	protected function __wakeup(){}
	protected function __sleep(){}

	public static function __callStatic($name, $arg) {
		$matches = array();
		if(preg_match('/^([a-z]+)([A-Z]+[A-z_]*)$/', $name, $matches) !== 1) {
			throw new BalooException('Invalid static call "'.$name.'" in '.get_class($this));			
		}
		$var = $matches[1];
		if(isset($matches[2])) {
			$key = strtolower($matches[2]);
			return static::${$var}[$key];
		} else {
			return static::${$var};
		}
    }
}