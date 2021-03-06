<?php

namespace Baloo;

/*
 * class DataEntityType
 * Class that implements entity type (data with same properties)
 *
 * @package baloo
 */

class DataEntityType
{
    protected $id = null;
    protected $name = null;

    /**
     * Constructor.
     *
     * @param integer $identifier Name or ID of entity type object to get,
     *              can be Null if called from PDO query (default=null)
     */
    public function __construct($identifier = null)
    {
        if (is_null($this->id) === true) {
            if (is_int($identifier) === false) {
                $this->name = $identifier;
                $this->id = self::getEntityTypeIdByName($this->name);
            } else {
                $this->id = $identifier;
                $this->name = self::getEntityTypeNameById($this->id);
            }
        }
        $this->id = intval($this->id); // force id as integer
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Give the entity type name for a given ID.
     *
     * @static
     *
     * @param integer $id
     * @return int|false ID of entity type or error
     */
    public static function getEntityTypeNameById($id)
    {
        $query = BalooContext::getInstance()->getPDO()->prepare('
        SELECT name
        FROM '.BalooModel::tableEntityType().' AS _TYPE
        WHERE _TYPE.id='.$id);
        $query->execute();

        return $query->fetchColumn();
    }

    /**
     * Give the entity type ID for a given name.
     *
     * @static
     *
     * @return string|false Name of entity type or error
     */
    public static function getEntityTypeIdByName($name)
    {
        $query = BalooContext::getInstance()->getPDO()->prepare('
        SELECT id
        FROM '.BalooModel::tableEntityType()." AS _TYPE
        WHERE _TYPE.name='".$name."'");
        $query->execute();

        return $query->fetchColumn();
    }

    /**
     * Get specified entity type from current datasource.
     *
     * @param string $typeName Entity type name to get
     *
     * @return DataEntityType|false DataEntityType object or error
     *
     * @todo Refactor code (moved from DataSource)
     */
    public function getEntityTypeByName($typeName)
    {
        $query = static::$pdo->prepare('
        SELECT _TYPE.id as id, _TYPE.name as name, _SOURCE.name AS datasourcename
        FROM '.BalooModel::tableEntityType().' AS _TYPE
        INNER JOIN '.BalooModel::tableDataSource().' AS _SOURCE
        ON _SOURCE.id=_TYPE.'.BalooModel::tableDataSource().'_id
        WHERE _TYPE.'.BalooModel::tableDataSource().'_id='.$this->id."
        AND _TYPE.name='${typeName}'");
        $query->setFetchMode(\PDO::FETCH_CLASS, __NAMESPACE__.'\DataEntityType');
        $query->execute();

        $result = $query->fetch(\PDO::FETCH_CLASS);
        if (is_null($result)) {
            $result = false;
        }

        return $result;
    }


    /**
     * Give the list of properties for current entity type.
     *
     * @return array|false Array of properties or error
     */
    public function getEntityTypePropertyList()
    {
        $query = BalooContext::getInstance()->getPDO()->prepare('
        SELECT _FIELD.name AS name, _FIELD.custom AS iscustom, _PROP.name AS type, _PROP.format AS format
        FROM '.BalooModel::tableEntityField().' AS _FIELD
        INNER JOIN '.BalooModel::tableEntityType().' AS _TYPE
        ON _TYPE.id=_FIELD.'.BalooModel::tableEntityType().'_id
        INNER JOIN '.BalooModel::tableEntityFieldInfo().' AS _PROP
        ON _PROP.id=_FIELD.'.BalooModel::tableEntityFieldInfo().'_id
        WHERE _TYPE.id='.$this->id);
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param integer $name
     */
    public static function getTypePropertyId($name)
    {
        $query = BalooContext::getInstance()->getPDO()->prepare('
        SELECT id
        FROM '.BalooModel::tableEntityFieldInfo().'
        WHERE name=:name
        ');
        $query->execute(array(':name' => $name));

        return (integer) $query->fetchColumn();
    }

    /**
     * Give the list of existing property types.
     *
     * @static
     *
     * @return array|false array[id,name] of property types or error
     */
    public static function getPropertyTypesList()
    {
        $query = BalooContext::getInstance()->getPDO()->prepare('
        SELECT _PROP.id AS id, _PROP.name AS name, _PROP.format AS format
        FROM '.BalooModel::tableEntityFieldInfo().' AS _PROP
        ');
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get property information by property's name for current entity type.
     *
     * @return mixed|false Name ou ID of property or error
     */
    public function getEntityTypePropertyInfo($identifier)
    {
        if (is_int($identifier)) {
            $propertyIdentifier = 'id='.$identifier;
        } else {
            $propertyIdentifier = "name='".$identifier."'";
        }
        $query = BalooContext::getInstance()->getPDO()->prepare('
        SELECT _PROP.name AS type, _PROP.format AS format
        FROM '.BalooModel::tableEntityField().' AS _FIELD
        INNER JOIN '.BalooModel::tableEntityType().' AS _TYPE
        ON _TYPE.id=_FIELD.'.BalooModel::tableEntityType().'_id
        INNER JOIN '.BalooModel::tableEntityFieldInfo().' AS _PROP
        ON _PROP.id=_FIELD.'.BalooModel::tableEntityFieldInfo().'_id
        WHERE _TYPE.id='.$this->id.'
        AND _FIELD.'.$propertyIdentifier);
        $query->execute();

        $result = $query->fetch(\PDO::FETCH_ASSOC);
        if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * Give the list of existing entities for current type.
     *
     * @param bool $excludeChildObject Set if child entities are exclude from list (default=true)
     *
     * @return array|false Array of DataEntity or error
     */
    public function getEntityList($excludeChildObject = true)
    {
        $condition = '';
        if ($excludeChildObject === true) {
            $condition .= ' AND _DATA.parent_id=0';
        }
        $query = BalooContext::getInstance()->getPDO()->prepare('
        SELECT _DATA.id, _DATA.'.BalooModel::tableEntityType().'_id as typeId
        FROM '.BalooModel::tableEntityObject().' AS _DATA
        INNER JOIN '.BalooModel::tableEntityType().' AS _TYPE
        ON _TYPE.id = _DATA.'.BalooModel::tableEntityType().'_id
        WHERE _TYPE.id='.$this->id.$condition);
        $query->execute();

        return $query->fetchAll(\PDO::FETCH_CLASS, __NAMESPACE__.'\DataEntity');
    }

    /**
     * Give the list of existing entities for current type filtered on properties value
     *    getEntityByPropertyValue(array('summary' => 'demo', 'description' => 'demo'))
     *      will return objects with properties 'summary' = 'demo' AND 'description' = 'demo'
     *    getEntityByPropertyValue(array('summary' => array('demo', 'subject')))
     *      will return objects with property 'summary' = 'demo' OR 'subject'.
     *
     * @param mixed[] $filter             Array(propertie,value) of property filters (default=null).
     * @param bool    $excludeChildObject Set if child entities are exclude from list (default=true)
     *
     * @return array|false Array of DataEntity or error
     */
    public function getEntityByPropertyValue($filter = null, $excludeChildObject = true)
    {
        if (is_null($filter) === false || is_array($filter) === false) {
            $filterCondition = '';
            $childCondition = '';

            /* Properties SQL criteria */
            foreach ($filter as $propertie => $values) {
                if (is_array($values) === false) {
                    $values = array($values);
                }
                // parse value list of each properties and return a SQL sub criteria
                $valueQuery = array_reduce(
                    $values,
                    function($query, $value) {
                        if ($query != '') {
                            $query .= ' OR ';
                        }

                        return $query .= "_VALUE.value='".$value."'";
                    },
                    ''
                );
                // prepare field/value SQL criteria
                $filterQuery = '
               _DATA.id IN (
               SELECT DISTINCT _VALUE.'.BalooModel::tableEntityObject().'_id
               FROM '.BalooModel::tableEntityObjectValue().' AS _VALUE
               INNER JOIN '.BalooModel::tableEntityField().' AS _FIELD
               ON _FIELD.id=_VALUE.'.BalooModel::tableEntityField()."_id
               WHERE (_FIELD.name='".$propertie."' AND ".$valueQuery.'))';
                if ($filterCondition != '') {
                    $filterCondition .= ' AND ';
                }
                $filterCondition .= $filterQuery;
            }

            /* Child entities SQL criteria */
            if ($excludeChildObject === true) {
                $childCondition .= ' AND _DATA.parent_id=0';
            }

            $query = BalooContext::getInstance()->getPDO()->prepare('
            SELECT DISTINCT _DATA.id, _DATA.'.BalooModel::tableEntityType().'_id AS typeId
            FROM '.BalooModel::tableEntityObject().' AS _DATA
            INNER JOIN '.BalooModel::tableEntityType().' AS _TYPE
            ON _TYPE.id = _DATA.'.BalooModel::tableEntityType().'_id
            WHERE _TYPE.id='.$this->id.'
            AND '.$filterCondition.$childCondition);
            $query->execute();

            return $query->fetchAll(\PDO::FETCH_CLASS, __NAMESPACE__.'\DataEntity');
        }

        return false;
    }
}
