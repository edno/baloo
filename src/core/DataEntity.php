<?php
namespace Baloo;

/**
 * class DataEntity
 * Class that implements entity (formal data object)
 *
 * @package baloo
 */

//@ TODO add a table with BLOB column for managing complex objects
class DataEntity {

  protected $id = null;
  protected $typeId = null;
  protected $typeName = null;
  protected $properties = array();

  private $smartProperties = true; // dynamic cast of property's value

  /**
   * Constructor
   * @param int $id ID of entity object to get, can be Null if called from PDO query (default=null)
   */
  public function __construct($identifier = null) {
    $this->id = intval(is_null($identifier) ? $this->id : $identifier); // force id as integer
    if(is_null($this->typeId) === true){
      $this->_getEntityType();
    }
    $this->typeId = intval($this->typeId); // force typeId as integer
    $this->typeName = DataEntityType::getEntityTypeNameById($this->typeId);
    $this->_getEntityProperties();
  }

  public function __toString() {
    return $this->id;
  }

  /**
    * Magic accessor SET
    *
    * @todo Ability to add custom object as object.ToString (retreive it thru constructor) or as serialized object (retreive it by unserialize)
    */
    public function __set($name, $value) {
        $this->properties[$name] = (string)$value;
    }

  /**
    * Magic accessor GET
    *
    * @link http://php.net/manual/en/function.settype.php
    */
    public function __get($name) {
    $value = $this->properties[$name];

    if($this->smartProperties === true) {
      $entityType = new DataEntityType($this->typeId);
      $property = $entityType->getEntityTypePropertyInfo($name);
      switch($property['type']) {
        case 'object':
          $value = new $property['format']($value); // create new object
          break;
        case 'serializedobject':
          $value = unserialize($value);
          break;
        case 'datetime':
          $value = new DateTime($value);
          break;
        default:
          @settype($value, $property['type']); // if settype failed then default type is string (warning message disabled)
      }
    }

    return $value;
    }

  /**
   * Enable dynamic property's value casting (based on table entityfieldtype)
   *
   * @see __get()
   *
   * @access  public
   * @return  none
   */
  public function enableSmartProperties() {
    $this->smartProperties = true;
  }

  /**
   * Disable dynamic property's value casting (based on table entityfieldtype)
   *
   * @see __get()
   *
   * @access  public
   * @return  none
   */
  public function disableSmartProperties() {
    $this->smartProperties = false;;
  }

  /**
   * Check if current entity has child entity
   *
   * @access  public
   * @return  bool True if children exist, or False if not
   */
  public function hasChildren() {
    $query = BalooContext::$pdo->prepare("
      SELECT id
      FROM ". BalooModel::tableEntityObject() ."
      WHERE parent_id=". $this->id
      );
    $query->execute();

    return ($query->rowCount() > 0);
  }

  /**
   * Give the list of children entities for current entity
   *
   * @access  public
   * @return  array|false Array of children DataEntity or error
   */
  public function getChildren() {
    $query = BalooContext::$pdo->prepare("
      SELECT _DATA.id, _DATA.". BalooModel::tableEntityType() ."_id as typeId
      FROM ". BalooModel::tableEntityObject() ." AS _DATA
      INNER JOIN ". BalooModel::tableEntityType() ." AS _TYPE
      ON _TYPE.id = _DATA.". BalooModel::tableEntityType() ."_id
      WHERE _DATA.id IN (
        SELECT id
        FROM ". BalooModel::tableEntityObject() ."
        WHERE parent_id=". $this->id .")"
      );
    $query->execute();

    return $query->fetchAll(\PDO::FETCH_CLASS, 'DataEntity');
  }

  /**
   * Check if current entitie is a child entity
   *
   * @access  public
   * @return  bool True if is a child, or False if not
   */
  public function isChild() {
    $query = BalooContext::$pdo->prepare("
      SELECT parent_id
      FROM ". BalooModel::tableEntityObject() ."
      WHERE id=". $this->id
      );
    $query->execute();

    return (true && $query->fetchColumn());
  }

  /**
   * Give the parent entity for current entity
   *
   * @access  public
   * @return  DataEntity|false DataEntiy object of parent entity or error
   */
  public function getParent() {
    $query = BalooContext::$pdo->prepare("
      SELECT _DATA.id, _DATA.". BalooModel::tableEntityType() ."_id as typeId
      FROM ". BalooModel::tableEntityObject() ." AS _DATA
      INNER JOIN ". BalooModel::tableEntityType() ." AS _TYPE
      ON _TYPE.id = _DATA.". BalooModel::tableEntityType() ."_id
      WHERE _DATA.id IN (
        SELECT parent_id
        FROM ". BalooModel::tableEntityObject() ."
        WHERE id=". $this->id .")"
      );
    $query->setFetchMode(PDO::FETCH_CLASS, 'DataEntity');
    $query->execute();

    return $query->fetch(PDO::FETCH_CLASS);
  }

  /**
   * Set the properties for current entity (stored in an array)
   *
   * @access  private
   * @return  bool True if success, False if error
   */
  private function _getEntityProperties() {
    $query = BalooContext::$pdo->prepare("
      SELECT _PROP.name, _VALUE.value
      FROM ". BalooModel::tableEntityObjectValue() ." AS _VALUE
      INNER JOIN ". BalooModel::BalooModel::tableEntityTypeFieldInfo()() ." AS _PROP
      ON _PROP.id = _VALUE.entityfield_id
      WHERE _VALUE.dataobject_id = ". $this->id
      );
    $query->execute();
    $results = $query->fetchAll(\PDO::FETCH_OBJ);
    foreach($results as $item) $this->{$item->name} = $item->value;

    return (true && $results);
  }

  /**
   * Set the entity type for current entity
   *
   * @access  private
   * @return  bool True if success, False if error
   */
  private function _getEntityType() {
    $query = BalooContext::$pdo->prepare("
      SELECT _TYPE.id AS id, _TYPE.name  AS name
      FROM ". BalooModel::tableEntityObject()." AS _DATA
      INNER JOIN ". BalooModel::tableEntityType() ." AS _TYPE
      ON _TYPE.id = _DATA.entitytype_id
      WHERE _DATA.id = ". $this->id
      );
    $query->execute();
    $result = $query->fetch(\PDO::FETCH_ASSOC);
    $this->typeId = $result['id'];
    $this->typeName = $result['name'];

    return (true && $result);
  }
}
