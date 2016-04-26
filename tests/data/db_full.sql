CREATE TABLE IF NOT EXISTS 'datasource' (id INTEGER PRIMARY KEY ASC, name TEXT, version TEXT);

CREATE TABLE IF NOT EXISTS 'entitytype' (id INTEGER PRIMARY KEY ASC, name TEXT, datasource_id INTEGER);

CREATE TABLE IF NOT EXISTS 'entityfield' (id INTEGER PRIMARY KEY ASC, name TEXT, custom BOOLEAN, entitytype_id INTEGER, entityfieldtype_id INTEGER);

CREATE TABLE IF NOT EXISTS'entityfieldtype' (id INTEGER PRIMARY KEY ASC, name TEXT, format TEXT);

CREATE TABLE IF NOT EXISTS 'dataobject' (id INTEGER PRIMARY KEY ASC, entitytype_id INTEGER, entityfield_id INTEGER);

CREATE TABLE IF NOT EXISTS 'datavalue' (id INTEGER PRIMARY KEY ASC, value TEXT, entityfield_id INTEGER, dataobject_id INTEGER);

CREATE TABLE IF NOT EXISTS 'datavalueblob' (id INTEGER PRIMARY KEY ASC, value TEXT, entityfield_id INTEGER, dataobject_id INTEGER);
