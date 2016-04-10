<?php
namespace Baloo\PackMan;

/**
 * class PackMan
 * Class that provide package object
 * (mock object for PackMan operations)
 *
 * @package PackMan
 */

use Baloo\PackMan\PackManException;

class Package {

  public $name = '';
  public $datasourcetype = new StdClass();
  public $datasource = new StdClass();

  public function __construct(string $name, string $datasourcetype, string $datasource) {
    if( (is_null($name) || is_null($datasourcetype) || is_null($datasource)) === false ) {
  		$this->name = $name;
      $this->datasourcetype->name = $datasourcetype;
      $this->datasource->name = $datasource;
    } else {
      throw new PackManException("Invalid Constructor Arguments", 0);
    }
	}

}
