CREATE TABLE 'datasourcetype' (id INTEGER PRIMARY KEY ASC, name TEXT, version TEXT);

CREATE TABLE 'datasource' (id INTEGER PRIMARY KEY ASC, name TEXT, version TEXT, datasourcetype_id INTEGER);

CREATE TABLE 'entitytype' (id INTEGER PRIMARY KEY ASC, name TEXT, datasource_id INTEGER);

CREATE TABLE 'entityfield' (id INTEGER PRIMARY KEY ASC, name TEXT, custom TEXT, entitytype_id INTEGER, entityfieldtype_id INTEGER);

CREATE TABLE 'entityfieldtype' (id INTEGER PRIMARY KEY ASC, name TEXT, format TEXT);

CREATE TABLE 'dataobject' (id INTEGER PRIMARY KEY ASC, entitytype_id INTEGER, entityfield_id INTEGER);

CREATE TABLE 'datavalue' (id INTEGER PRIMARY KEY ASC, value TEXT, entityfield_id INTEGER, dataobject_id INTEGER);

CREATE TABLE 'datablob' (id INTEGER PRIMARY KEY ASC, value TEXT, entityfield_id INTEGER, dataobject_id INTEGER);

CREATE TABLE 'datafile' (id INTEGER PRIMARY KEY ASC, value TEXT, location TEXT, entityfield_id INTEGER, dataobject_id INTEGER);
