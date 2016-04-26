DROP TABLE IF EXISTS 'datasource';
CREATE TABLE 'datasource' (
    'id' INTEGER PRIMARY KEY ASC,
    'name' TEXT,
    'version' TEXT
);
DROP TABLE IF EXISTS 'entitytype';
CREATE TABLE 'entitytype' (
    'id' INTEGER PRIMARY KEY ASC,
    'name' TEXT,
    'datasource_id' INTEGER
);
DROP TABLE IF EXISTS 'entityfield';
CREATE TABLE 'entityfield' (
    'id' INTEGER PRIMARY KEY ASC,
    'name' TEXT,
    'custom' BOOLEAN,
    'entitytype_id' INTEGER,
    'entityfieldtype_id' INTEGER
);
DROP TABLE IF EXISTS 'entityfieldtype';
CREATE TABLE 'entityfieldtype' (
    'id' INTEGER PRIMARY KEY ASC,
    'name' TEXT,
    'format' TEXT
);
