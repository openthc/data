--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: b2b_path; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.b2b_path (
    license_id_source character varying(26) NOT NULL,
    license_id_target character varying(26) NOT NULL,
    meta jsonb,
    pair character varying(64)
);


ALTER TABLE public.b2b_path OWNER TO openthc;

--
-- Name: b2b_sale; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.b2b_sale (
    id character varying(26) NOT NULL,
    license_id_source character varying(26),
    license_id_target character varying(26),
    execute_at timestamp with time zone,
    stat character varying(32),
    full_price numeric(12,2),
    meta jsonb
);


ALTER TABLE public.b2b_sale OWNER TO openthc;

--
-- Name: b2b_sale_item; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.b2b_sale_item (
    id character varying(26) NOT NULL,
    b2b_sale_id character varying(26),
    lot_id_source character varying(26),
    lot_id_target character varying(26),
    qom_tx numeric(16,3),
    qom_rx numeric(16,3),
    uom character varying(8),
    full_price numeric(12,2),
    stat character varying(32)
);


ALTER TABLE public.b2b_sale_item OWNER TO openthc;

--
-- Name: lot; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.lot (
    id character varying(26) NOT NULL,
    license_id character varying(26) NOT NULL,
    product_id character varying(26) NOT NULL,
    variety_id character varying(26),
    section_id character varying(26),
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone,
    stat integer DEFAULT 200 NOT NULL,
    flag integer DEFAULT 0 NOT NULL,
    qty numeric(16,4),
    meta jsonb
);


ALTER TABLE public.lot OWNER TO openthc;

--
-- Name: product; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.product (
    id character varying(26) NOT NULL,
    license_id character varying(26) NOT NULL,
    product_type character varying(32) NOT NULL,
    package_type character varying(32) NOT NULL,
    package_size numeric(9,3),
    package_unit character varying(8),
    name character varying(256)
);


ALTER TABLE public.product OWNER TO openthc;

--
-- Name: b2b_sale_item_full; Type: VIEW; Schema: public; Owner: openthc
--

CREATE VIEW public.b2b_sale_item_full AS
 SELECT b2b_sale.id,
    b2b_sale.execute_at,
    b2b_sale.license_id_source,
    b2b_sale.license_id_target,
    b2b_sale.stat,
    b2b_sale.full_price AS sale_full_price,
    b2b_sale.unit_price AS sale_unit_price,
    b2b_sale_item.lot_id_source,
    b2b_sale_item.lot_id_target,
    b2b_sale_item.full_price AS sale_item_full_price,
    b2b_sale_item.qom_tx AS qty_tx,
    b2b_sale_item.qom_rx AS qty_rx,
    product_source.product_type,
    product_source.name AS product_name,
    product_source.package_size
   FROM (((public.b2b_sale
     JOIN public.b2b_sale_item ON (((b2b_sale.id)::text = (b2b_sale_item.b2b_sale_id)::text)))
     JOIN public.lot lot_source ON (((b2b_sale_item.lot_id_source)::text = (lot_source.id)::text)))
     JOIN public.product product_source ON (((lot_source.product_id)::text = (product_source.id)::text)));


ALTER TABLE public.b2b_sale_item_full OWNER TO openthc;

--
-- Name: b2c_sale; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.b2c_sale (
    id character varying(26) NOT NULL,
    license_id character varying(26) NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone,
    stat integer DEFAULT 200 NOT NULL,
    flag integer DEFAULT 0 NOT NULL,
    full_price numeric(12,2),
    meta jsonb
);


ALTER TABLE public.b2c_sale OWNER TO openthc;

--
-- Name: b2c_sale_item; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.b2c_sale_item (
    id character varying(26) NOT NULL,
    b2c_sale_id character varying(26) NOT NULL,
    lot_id character varying(26) NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone,
    stat integer DEFAULT 200 NOT NULL,
    flag integer DEFAULT 0 NOT NULL,
    qty numeric(16,4) NOT NULL,
    unit_price numeric(12,4) NOT NULL,
    package_size numeric(16,4),
    package_unit character varying(8),
    product_type character varying(256),
    meta jsonb
);


ALTER TABLE public.b2c_sale_item OWNER TO openthc;

--
-- Name: b2c_sale_item_full; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.b2c_sale_item_full (
    id character varying(26),
    supply_license_id character varying(26),
    retail_license_id character varying(26),
    b2b_sale_date timestamp without time zone,
    b2c_sale_date timestamp without time zone,
    product_name character varying(256),
    variety_name character varying(256),
    b2b_unit_price numeric(16,4),
    b2c_unit_count numeric(16,4),
    b2c_unit_price numeric(16,4)
);


ALTER TABLE public.b2c_sale_item_full OWNER TO openthc;

--
-- Name: company; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.company (
    id character varying(26) NOT NULL,
    name character varying(256)
);


ALTER TABLE public.company OWNER TO openthc;

--
-- Name: contact; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.contact (
    id character varying(26),
    ulid character varying(26),
    name character varying(256),
    email character varying(256)
);


ALTER TABLE public.contact OWNER TO openthc;

--
-- Name: lab_result; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.lab_result (
    id character varying(26) NOT NULL,
    license_id character varying(26) NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone,
    stat integer DEFAULT 200 NOT NULL,
    flag integer DEFAULT 0 NOT NULL,
    type character varying(64) NOT NULL,
    cbd numeric(12,4),
    thc numeric(12,4),
    meta jsonb
);


ALTER TABLE public.lab_result OWNER TO openthc;

--
-- Name: lab_result_ext; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.lab_result_ext (
    id character varying(26) NOT NULL,
    sample_id character varying(26)
);


ALTER TABLE public.lab_result_ext OWNER TO openthc;

--
-- Name: lab_result_lot; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.lab_result_lot (
    lab_result_id character varying(26) NOT NULL,
    lot_id character varying(26) NOT NULL,
    type character varying(32)
);


ALTER TABLE public.lab_result_lot OWNER TO openthc;

--
-- Name: license; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.license (
    id character varying(26) NOT NULL,
    type character(1),
    code character varying(16),
    name character varying(256),
    lat numeric(14,10),
    lon numeric(14,10),
    tsp character varying(16),
    company_id character varying(26),
    address_meta jsonb
);


ALTER TABLE public.license OWNER TO openthc;

--
-- Name: license_contact; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.license_contact (
    license_id character varying(26) NOT NULL,
    contact_id character varying(26) NOT NULL
);


ALTER TABLE public.license_contact OWNER TO openthc;

--
-- Name: license_history; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.license_history (
    id bigint NOT NULL,
    license_id character varying(26) NOT NULL,
    license_ulid character varying(26) NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    type character varying(32),
    name character varying(256),
    meta jsonb
);


ALTER TABLE public.license_history OWNER TO openthc;

--
-- Name: license_history_id_seq; Type: SEQUENCE; Schema: public; Owner: openthc
--

CREATE SEQUENCE public.license_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.license_history_id_seq OWNER TO openthc;

--
-- Name: license_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: openthc
--

ALTER SEQUENCE public.license_history_id_seq OWNED BY public.license_history.id;


--
-- Name: license_revenue; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.license_revenue (
    id bigint NOT NULL,
    license_id character varying(26),
    source character varying(8),
    month date,
    rev_amount numeric(17,2),
    tax_amount numeric(17,2)
);


ALTER TABLE public.license_revenue OWNER TO openthc;

--
-- Name: license_revenue_full; Type: VIEW; Schema: public; Owner: openthc
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


ALTER TABLE public.license_revenue_full OWNER TO openthc;

--
-- Name: license_revenue_id_seq; Type: SEQUENCE; Schema: public; Owner: openthc
--

CREATE SEQUENCE public.license_revenue_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.license_revenue_id_seq OWNER TO openthc;

--
-- Name: license_revenue_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: openthc
--

ALTER SEQUENCE public.license_revenue_id_seq OWNED BY public.license_revenue.id;


--
-- Name: product_license_name; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product_license_name (
    lot_count bigint,
    license_id character varying(26),
    name character varying(256)
);


ALTER TABLE public.product_license_name OWNER TO postgres;

--
-- Name: variety; Type: TABLE; Schema: public; Owner: openthc
--

CREATE TABLE public.variety (
    id character varying(26) NOT NULL,
    license_id character varying(26) NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone,
    stat integer DEFAULT 200 NOT NULL,
    flag integer DEFAULT 0 NOT NULL,
    hash character varying(64) NOT NULL,
    name character varying(256) NOT NULL,
    meta jsonb
);


ALTER TABLE public.variety OWNER TO openthc;

--
-- Name: license_history id; Type: DEFAULT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.license_history ALTER COLUMN id SET DEFAULT nextval('public.license_history_id_seq'::regclass);


--
-- Name: license_revenue id; Type: DEFAULT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.license_revenue ALTER COLUMN id SET DEFAULT nextval('public.license_revenue_id_seq'::regclass);


--
-- Name: b2b_path b2b_path_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.b2b_path
    ADD CONSTRAINT b2b_path_pkey PRIMARY KEY (license_id_source, license_id_target);


--
-- Name: b2c_sale_item b2c_sale_item_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.b2c_sale_item
    ADD CONSTRAINT b2c_sale_item_pkey PRIMARY KEY (id);


--
-- Name: b2c_sale b2c_sale_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.b2c_sale
    ADD CONSTRAINT b2c_sale_pkey PRIMARY KEY (id);


--
-- Name: company company_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.company
    ADD CONSTRAINT company_pkey PRIMARY KEY (id);


--
-- Name: lab_result_ext lab_result_ext_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.lab_result_ext
    ADD CONSTRAINT lab_result_ext_pkey PRIMARY KEY (id);


--
-- Name: lab_result_lot lab_result_lot_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.lab_result_lot
    ADD CONSTRAINT lab_result_lot_pkey PRIMARY KEY (lot_id, lab_result_id);


--
-- Name: lab_result lab_result_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.lab_result
    ADD CONSTRAINT lab_result_pkey PRIMARY KEY (id);


--
-- Name: license_history license_history_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.license_history
    ADD CONSTRAINT license_history_pkey PRIMARY KEY (id);


--
-- Name: license license_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.license
    ADD CONSTRAINT license_pkey PRIMARY KEY (id);


--
-- Name: license_revenue license_revenue_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.license_revenue
    ADD CONSTRAINT license_revenue_pkey PRIMARY KEY (id);


--
-- Name: lot lot_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.lot
    ADD CONSTRAINT lot_pkey PRIMARY KEY (id);


--
-- Name: product product_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.product
    ADD CONSTRAINT product_pkey PRIMARY KEY (id);


--
-- Name: variety variety_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.variety
    ADD CONSTRAINT variety_pkey PRIMARY KEY (id);


--
-- Name: b2b_sale_item transfer_item_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.b2b_sale_item
    ADD CONSTRAINT transfer_item_pkey PRIMARY KEY (id);


--
-- Name: b2b_sale transfer_pkey; Type: CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.b2b_sale
    ADD CONSTRAINT transfer_pkey PRIMARY KEY (id);


--
-- Name: transfer_item_b2b_sale_id_idx; Type: INDEX; Schema: public; Owner: openthc
--

CREATE INDEX transfer_item_b2b_sale_id_idx ON public.b2b_sale_item USING btree (b2b_sale_id);


--
-- Name: license_revenue_full _RETURN; Type: RULE; Schema: public; Owner: openthc
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


--
-- Name: license_history license_history_license_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.license_history
    ADD CONSTRAINT license_history_license_id_fkey FOREIGN KEY (license_id) REFERENCES public.license(id);


--
-- Name: license_revenue license_revenue_license_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: openthc
--

ALTER TABLE ONLY public.license_revenue
    ADD CONSTRAINT license_revenue_license_id_fkey FOREIGN KEY (license_id) REFERENCES public.license(id);


--
-- Name: TABLE b2b_path; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.b2b_path TO openthc_ro;


--
-- Name: TABLE b2b_sale; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.b2b_sale TO openthc_ro;


--
-- Name: TABLE b2b_sale_carrier; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.b2b_sale_carrier TO openthc_ro;


--
-- Name: TABLE b2b_sale_item; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.b2b_sale_item TO openthc_ro;


--
-- Name: TABLE lot; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.lot TO openthc_ro;


--
-- Name: TABLE product; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.product TO openthc_ro;


--
-- Name: TABLE b2b_sale_item_full; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.b2b_sale_item_full TO openthc_ro;


--
-- Name: TABLE b2c_sale; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.b2c_sale TO openthc_ro;


--
-- Name: TABLE b2c_sale_item; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.b2c_sale_item TO openthc_ro;


--
-- Name: TABLE b2c_sale_item_full; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.b2c_sale_item_full TO openthc_ro;


--
-- Name: TABLE company; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.company TO openthc_ro;


--
-- Name: TABLE contact; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.contact TO openthc_ro;


--
-- Name: TABLE lab_result; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.lab_result TO openthc_ro;


--
-- Name: TABLE lab_result_ext; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.lab_result_ext TO openthc_ro;


--
-- Name: TABLE lab_result_lot; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.lab_result_lot TO openthc_ro;


--
-- Name: TABLE license; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.license TO openthc_ro;


--
-- Name: TABLE license_contact; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.license_contact TO openthc_ro;


--
-- Name: TABLE license_history; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.license_history TO openthc_ro;


--
-- Name: TABLE license_revenue; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.license_revenue TO openthc_ro;


--
-- Name: TABLE license_revenue_full; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.license_revenue_full TO openthc_ro;


--
-- Name: TABLE product_license_name; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.product_license_name TO openthc_ro;


--
-- Name: TABLE variety; Type: ACL; Schema: public; Owner: openthc
--

GRANT SELECT ON TABLE public.variety TO openthc_ro;


--
-- PostgreSQL database dump complete
--
