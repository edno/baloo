<?php
namespace Baloo;

/**
 * class BalooException
 * Class that extend Exception class
 *
 * @package baloo
 */
class BalooException extends \Exception {

	public function __construct($message = "", $code = 0, $previous = null){
		parent::__construct($message, $code, $previous);
	}
}
