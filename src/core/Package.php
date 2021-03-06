<?php
namespace Baloo\Packman;

/**
 * class Package
 * Class that provide package object
 * (mock object for Packman operations)
 *
 * @package Packman
 */

use Baloo\BalooContext;

// @codingStandardsIgnoreStart
BalooContext::loadLibrary('json'); // workaround for non-class namespace
// @codingStandardsIgnoreEnd
use Baloo\Lib\Json;

class Package
{

    public $name;
    public $datasourcetype;
    public $datasource;

    public function __construct()
    {
        $vArgs = func_get_args();
        $nArgs = func_num_args();
        switch ($nArgs) {
            case 1: //__construct(string $jsonString)
                call_user_func_array(array($this, '__constructFromJSON'), $vArgs);
                break;
            case 3: //__construct(string $name, string $datasourcetype, string $datasource)
                call_user_func_array(array($this, '__constructDefault'), $vArgs);
                break;
            default:
                throw new PackmanException("Invalid Constructor Arguments");
        }
    }

    private function __constructFromJSON(string $jsonString)
    {
        try {
            if (Json\json_valid($jsonString)) {
                $pack = json_decode($jsonString);
                if ((isset($pack->name) && isset($pack->datasourcetype) && isset($pack->datasource)) === true) {
                    $this->name = $pack->name;
                    $this->datasourcetype = $pack->datasourcetype;
                    $this->datasource = $pack->datasource;
                } else {
                    throw new PackmanException("Invalid JSON Package");
                }
            }
        } catch (\Exception $e) {
            throw new PackmanException($e->getMessage());
        }
    }

    private function __constructDefault(string $name, string $datasourcetype, string $datasource)
    {
        try {
            if ((is_null($name) || is_null($datasourcetype) || is_null($datasource)) === false) {
                $this->name = $name;
                $this->datasourcetype = (object) [
                'name' => $datasourcetype
                ];
                $this->datasource = (object) [
                'name' => $datasource
                ];
            } else {
                throw new PackmanException("Invalid Constructor Arguments");
            }
        } catch (\Exception $e) {
            throw new PackmanException($e->getMessage());
        }
    }
}
