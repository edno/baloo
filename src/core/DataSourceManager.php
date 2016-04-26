<?php

namespace Baloo;

/*
 * class DataSourceManager
 * Class that manages datasource records into database
 *
 * @package baloo
 */

// @ TODO include ability to INSERT with IGNORE or UPDATE

// @ TODO check that only one INSERT transaction is used if several VALUES for the same TABLE

class DataSourceManager
{
    use Singleton;

    protected static $pdo = null;

    private function __init()
    {
        // instanciate PDO
        if (is_null(static::$pdo)) {
            static::$pdo = BalooContext::getInstance()->getPDO();
        }
    }

    /**
     * Give access to DataSource constructor thru datasource's name.
     *
     * @param string $name Datasource name to get
     *
     * @return DataSource|false DataSource object or error
     */
    public function getDataSourceByName($name)
    {
        $datasource = new DataSource($name);

        if ($datasource->getId() === 0) {
            return false;
        }

        return $datasource;
    }

    /**
     * Give the list of existing datasources.
     *
     * @return array|false array of datasource objects or error
     */
    public function getDataSourceList()
    {
        $query = static::$pdo->prepare('
        SELECT id, name
        FROM '.BalooModel::tableDataSource());
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_CLASS, __NAMESPACE__.'\DataSource');
    }

    public function deleteEntityTypes($datasource_name = null)
    {
        $query = static::$pdo->prepare('
        DELETE FROM '.BalooModel::tableEntityType().'
        WHERE EXISTS (
        SELECT 1
        FROM '.BalooModel::tableDataSource().' AS _SOURCE
        WHERE _SOURCE.name=:name
        AND _SOURCE.id='.BalooModel::tableEntityType().'.'.BalooModel::tableDataSource().'_id
        )
        ');

        return $query->execute([':name' => $datasource_name]);
    }

    public function deleteEntityProperties($datasource_name = null)
    {
        $query = static::$pdo->prepare('
        DELETE
        FROM '.BalooModel::tableEntityField().'
        WHERE EXISTS(
        SELECT 1
        FROM '.BalooModel::tableEntityType().' AS _TYPE
        INNER JOIN '.BalooModel::tableDataSource().' AS _SOURCE
        ON _SOURCE.id = _TYPE.'.BalooModel::tableDataSource().'_id
        WHERE _SOURCE.name=:name
        AND _TYPE.id = '.BalooModel::tableEntityField().'.'.BalooModel::tableEntityType().'_id
        )
        ');

        return $query->execute([':name' => $datasource_name]);
    }

    public function deleteDataSource($datasource_name = null)
    {
        $query = static::$pdo->prepare('
        DELETE FROM '.BalooModel::tableDataSource().'
        WHERE name=:name
        ');

        return $query->execute([':name' => $datasource_name]);
    }

    public function deleteDataSourceType($datasourcetype_name = null)
    {
        $query = static::$pdo->prepare('
        DELETE FROM '.BalooModel::tableDataSourceType().'
        WHERE EXISTS(
        SELECT 1
        FROM '.BalooModel::tableDataSource().' AS _SOURCE
        WHERE _SOURCE.name=:name
        AND '.BalooModel::tableDataSourceType().'.id=_SOURCE.'.BalooModel::tableDataSourceType().'_id
        )
        ');

        return $query->execute([':name' => $datasourcetype_name]);
    }

    public function addDataSourceType($name, $version)
    {
        $query = static::$pdo->prepare('
        INSERT INTO '.BalooModel::tableDataSourceType().' (name, version)
        VALUES (:name, :version)
        ');

        return $query->execute([':name' => $name, ':version' => $version]);
    }

    public function addDataType($datasource, $name)
    {
        $query = static::$pdo->prepare('
        INSERT INTO '.BalooModel::tableEntityType().' (name, '.BalooModel::tableDataSource().'_id)
        VALUES (:name, :datasource_id)
        ');
        $query->execute([':name' => $name, ':datasource_id' => $datasource]);

        return static::$pdo->lastInsertId();
    }

    public function addDataTypeField($type, $name, $typefield = 0, $custom = 0)
    {
        $typefield = DataEntityType::getTypePropertyId($typefield);
        $query = static::$pdo->prepare('
        INSERT INTO '.BalooModel::tableEntityField().'
        (name, custom, '.BalooModel::tableEntityType().'_id, '.BalooModel::tableEntityFieldInfo().'_id)
        VALUES (:name, :custom, :entitytype_id, :entityfieldtype_id)
        ');

        return $query->execute([':name' => $name,
                                ':custom' => (bool) $custom,
                                ':entitytype_id' => (integer) $type,
                                ':entityfieldtype_id' => (integer) $typefield]);
    }

    public function addDataTypeFieldType($name, $format = null)
    {
        $query = static::$pdo->prepare('
        INSERT INTO '.BalooModel::tableEntityFieldInfo().' (name, format)
        VALUES (:name, :format)
        ');

        return $query->execute([':name' => $name, ':format' => $format]);
    }
}
