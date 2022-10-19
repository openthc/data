CREATE VIEW public.b2b_sale_item_full AS
 SELECT b2b_sale.id,
    b2b_sale.execute_at,
    b2b_sale.source_license_id,
    b2b_sale.target_license_id,
    b2b_sale.stat,
    b2b_sale_item.full_price AS sale_full_price,
    b2b_sale_item.unit_price AS sale_unit_price,
    b2b_sale_item.lot_id_source,
    b2b_sale_item.lot_id_target,
    b2b_sale_item.full_price AS sale_item_full_price,
    b2b_sale_item.unit_count_tx AS qty_tx,
    b2b_sale_item.unit_count_rx AS qty_rx,
    product_source.product_type,
    product_source.name AS product_name,
    product_source.package_size
   FROM (((public.b2b_sale
     JOIN public.b2b_sale_item ON (((b2b_sale.id)::text = (b2b_sale_item.b2b_sale_id)::text)))
     LEFT JOIN public.lot lot_source ON (((b2b_sale_item.lot_id_source)::text = (lot_source.id)::text)))
     LEFT JOIN public.product product_source ON (((lot_source.product_id)::text = (product_source.id)::text)));



--
-- Name: license_revenue_full; Type: VIEW;
--

CREATE VIEW public.license_revenue_full AS
SELECT
    NULL::character varying(26) AS company_id,
    NULL::character varying(256) AS company_name,
    NULL::character varying(26) AS license_id,
    NULL::character varying(256) AS license_name,
    NULL::character varying(16) AS license_code,
    NULL::character(1) AS license_type,
    NULL::date AS month,
    NULL::numeric AS rev_amount_sum,
    NULL::numeric AS tax_amount_sum,
    NULL::text AS city,
    NULL::text AS county;


--
-- Name: license_revenue_full _RETURN; Type: RULE;
--

CREATE OR REPLACE VIEW public.license_revenue_full AS
 SELECT company.id AS company_id,
    company.name AS company_name,
    license.id AS license_id,
    license.name AS license_name,
    license.code AS license_code,
    license.type AS license_type,
    license_revenue.month,
    license_revenue.source,
    sum(license_revenue.rev_amount) AS rev_amount_sum,
    sum(license_revenue.tax_amount) AS tax_amount_sum,
    (license.address_meta ->> 'city'::text) AS city,
    (license.address_meta ->> 'county'::text) AS county
   FROM ((public.company
     JOIN public.license ON (((company.id)::text = (license.company_id)::text)))
     JOIN public.license_revenue ON (((license.id)::text = (license_revenue.license_id)::text)))
  GROUP BY company.id, company.name, license.id, license.name, license_revenue.month, license_revenue.source, (license.address_meta ->> 'city'::text), (license.address_meta ->> 'county'::text);
