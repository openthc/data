<?php
/**
 */

// wip_lot_retail_lab_result => lot_lab_result_retail_cache

// CREATE TABLE wip_lot_supply_lab_result AS
// SELECT id, created_at, updated_at, substr(id::text, 1, 9) AS license_code, meta->>'lab_result_id' AS lab_result_id, meta
// FROM lot WHERE (id LIKE 'WAG%' OR id LIKE 'WAJ%' OR id LIKE 'WAM%') AND created_at >= '2019-01-01';

$sql = <<<SQL
CREATE TABLE lot_lab_report_retail_cache (
	id varchar(26) primary key,
	lab_report_id varchar(26),
	license_origin varchar(26),
	license_retail varchar(26),
	product_id_origin varchar(26),
	product_id_retail varchar(26),
	created_at timestamp with time zone,
	updated_at timestamp with time zone,
	meta jsonb
);
SQL;
echo "$sql\n";
// $dbc->query($sql);

$sql = <<<SQL
INSERT INTO lot_lab_report_retail_cache (id, lab_report_id, license_origin, license_retail, created_at, meta)
SELECT
 id
 , meta->>'lab_result_id'
 , meta->>'created_by_mme_id'
 , license_id
 , created_at
 , meta
FROM lot
WHERE id LIKE 'WAR%' AND created_at >= '2019-01-01'
SQL;
echo "$sql\n";






// id                | character varying(26)    |           |          |
// created_at        | timestamp with time zone |           |          |
// updated_at        | timestamp with time zone |           |          |
// license_retail    | text                     |           |          |
// license_source    | text                     |           |          |
// lab_report_id     | text                     |           |          |
// meta              | jsonb                    |           |          |
// supply_product_id | character varying(26)    |           |          |
// retail_product_id | character varying(26)    |           |          |



$sql = <<<SQL
CREATE TABLE lab_report_supply_cache (
	id varchar(26) primary key,
	lab_report_id varchar(26),
	license_origin varchar(26),
	license_retail varchar(26),
	product_id_origin varchar(26),
	product_id_retail varchar(26),
	created_at timestamp with time zone,
	updated_at timestamp with time zone,
	meta jsonb
);
SQL;
echo "$sql\n";
