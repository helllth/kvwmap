BEGIN;

UPDATE `config` SET `group` = 'Administration' WHERE `config`.`name` IN('NORMALIZE_NULL_AREA', 'NORMALIZE_POINT_DISTANCE_THRESHOLD', 'NORMALIZE_ANGLE_THRESHOLD', 'NORMALIZE_AREA_THRESHOLD');

COMMIT;