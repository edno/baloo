<?php
namespace Baloo\PackMan;

/**
 * class PackManException
 * Class that manages PackMan exceptions
 *
 * @package PackageManager
 */

class PackManException  extends \Exception {

	public function __construct($message = "", $code = 0, $previous = null){
		parent::__construct($message, $code, $previous);
	}
}
