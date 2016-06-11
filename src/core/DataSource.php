<?php

namespace Baloo;

/**
 * class DataSource
 * Class that implements datasource (logical group of data).
 */
class DataSource
{
    protected static $pdo = null;

    private $id = null;
    private $name = null;
    private $version = null;

    /**
     * Constructor
     *
     */
    public function __construct($param = null)
    {
        // instanciate PDO
        if (is_null(static::$pdo)) {
            static::$pdo = BalooContext::getInstance()->getPDO();
        }

        // if param is string then set default instance name
        if (is_string($param) === true && empty(trim($param)) === false) {
            $this->name = trim($param);
        }

        // if param set, then try to retrieve existing datasource
        if (is_null($param) === false) {
            if (is_string($param)) { // if string, then retrieve by name
                $datasource = $this->__getDataSourceByName(trim($param));
            } elseif (is_numeric($param)) { // if numeric then retrieve by id
                $datasource = $this->__getDataSourceById(intval($param));
            } else { // if other type then throw exception
                throw new BalooException('Invalid parameter type '.gettype($param).' expected string or numeric');
            }
            // if valid datasource retrieved then copy properties
            if (isset($datasource) && $datasource) {
                $this->id = $datasource->getId();
                $this->name = $datasource->getName();
                $this->version = $datasource->getVersion();
            }
        }
        // if no valid name (no instance and invalid param) then throw exception
        if (is_null($this->name)) {
            throw new BalooException("Invalid data source name or id '${param}'");
        }
    }

    public function __toString()
    {
        return trim($this->getName().' '.$this->getVersion());
    }

    public function save()
    {
        if (is_null($this->id) === false) {
            $this->__updateDataSource();
        } else {
            $this->__insertDataSource();
        }
    }

    public function delete()
    {
        // @todo
        //http://stackoverflow.com/questions/5180446/how-to-delete-a-php-object-from-its-class/21367011#21367011
    }

    public function setName($name)
    {
        $name = trim($name);
        if (empty($name) === false && is_string($name) === true) {
            $this->name = $name;
        } else {
            throw new BalooException('Name must be non empty string!');
        }
    }

    public function setVersion($version)
    {
        $this->version = strval($version);
    }

    public function getId()
    {
        return intval($this->id);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return strval($this->version);
    }

    /**
     * Give the list of existing entity types for current datasource.
     *
     * @return array|false Array of DataEntityType objects or error
     */
    public function getEntityTypeList()
    {
        $query = static::$pdo->query('
        SELECT _TYPE.id as id, _TYPE.name as name, _SOURCE.name AS datasourcename
        FROM '.BalooModel::tableEntityType().' AS _TYPE
        INNER JOIN '.BalooModel::tableDataSource().' AS _SOURCE
        ON _SOURCE.id=_TYPE.'.BalooModel::tableDataSource().'_id
        WHERE _TYPE.'.BalooModel::tableDataSource().'_id='.$this->id);
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_CLASS, __NAMESPACE__.'\DataEntityType');
    }



    /**
     * Give the list of existing entities of any type for current datasource.
     *
     * @param bool $excludeChildObject Set if child entities are exclude from list (default=true)
     *
     * @return array|false Array of types and their entities or error
     */
    public function getEntityList($excludeChildObject = true)
    {
        $results = $this->getEntityTypeList();
        if ($results) {
            array_walk(
                $results,
                function(&$item, $key, $excludeChildObject) {
                    $item = [
                        'type' => $item,
                        'data' => $item->getEntityList($excludeChildObject)
                    ];
                },
                $excludeChildObject
            );
        }

        return $results;
    }

    /**
     * Give the list of existing entities of specified type for current datasource.
     *
     * @param string $typeName           Entity type name used to retrieve entities
     * @param bool   $excludeChildObject Set if child entities are exclude from returned list (default=true)
     *
     * @return array|false array[DataEntity] of DataEntity objects or error
     */
    public function getEntityListByType($typeName, $excludeChildObject = true)
    {
        return $this->getEntityTypeByName($typeName)->getEntityList($excludeChildObject);
    }

    private function __updateDataSource()
    {
        $query = static::$pdo->prepare('
        UPDATE '.BalooModel::tableDataSource().'
        SET name=:name, version=:version
        WHERE id='.$this->id);
        $result = $query->execute([':name' => $this->name, ':version' => $this->version]);

        if ($result === false) {
            throw new BalooException("Error Processing Request", 1);
        }
        return $result;
    }

    private function __insertDataSource()
    {
        $query = static::$pdo->prepare('
        INSERT INTO '.BalooModel::tableDataSource().' (name, version)
        VALUES (:name, :version)
        ');
        $result = $query->execute([':name' => $this->name, ':version' => $this->version]);

        $this->id = static::$pdo->lastInsertId();

        if ($result === false) {
            throw new BalooException("Error Processing Request", 1);
        }
        return $result;
    }

    /**
     * @param string $name
     */
    private function __getDataSourceByName($name)
    {
        return $this->__getDataSourceByColumn('name', $name);
    }

    /**
     * @param integer $id
     */
    private function __getDataSourceById($id)
    {
        return $this->__getDataSourceByColumn('id', $id);
    }

    /**
     * Retrieve existing datasource by name
     *
     *
     * @param string $column
     * @return DataSource|false DataSource object or error
     */
    private function __getDataSourceByColumn($column, $value)
    {
        $query = static::$pdo->prepare('
        SELECT _SOURCE.id AS id, _SOURCE.name AS name, _SOURCE.version AS version
        FROM '.BalooModel::tableDataSource()." AS _SOURCE
        WHERE _SOURCE.${column}=:value");
        $query->setFetchMode(\PDO::FETCH_CLASS, __NAMESPACE__.'\DataSource');
        $query->execute([':value' => $value]);

        $result = $query->fetch(\PDO::FETCH_CLASS);
        if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    private function __deleteDataSource()
    {

    }
}
