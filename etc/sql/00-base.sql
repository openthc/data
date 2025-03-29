--
-- PostgreSQL database dump
--

\c openthc_data

SET check_function_bodies = false;
SET client_encoding = 'UTF8';
SET client_min_messages = warning;
SET default_tablespace = '';
SET default_with_oids = false;
SET idle_in_transaction_session_timeout = 0;
SET lock_timeout = 0;
SET row_security = off;
SET search_path TO public;
SET standard_conforming_strings = on;
SET statement_timeout = 0;
SET xmloption = content;

--
-- Name: b2b_path; Type: TABLE;
--

CREATE TABLE public.b2b_path (
    source_license_id character varying(26) NOT NULL,
    target_license_id character varying(26) NOT NULL,
    meta jsonb,
    pair character varying(64)
);


--
-- Name: b2b_sale; Type: TABLE;
--

CREATE TABLE public.b2b_sale (
    id character varying(26) NOT NULL,
    source_license_id character varying(26),
    target_license_id character varying(26),
    execute_at timestamp with time zone,
    stat character varying(32),
    full_price numeric(16,4),
    meta jsonb
);


--
-- Name: b2b_sale_item; Type: TABLE;
--

CREATE TABLE public.b2b_sale_item (
    id character varying(26) NOT NULL,
    b2b_sale_id character varying(26),
    inventory_id_source character varying(26),
    inventory_id_target character varying(26),
    unit_count_tx numeric(16,3),
    unit_count_rx numeric(16,3),
    unit_price numeric(16,4),
    full_price numeric(16,4),
    uom character varying(8),
    stat character varying(32)
);


--
-- Name: b2b_sale_item_full; Type: TABLE;
--

CREATE TABLE public.b2b_sale_item_full (
    id character varying(26),
    source_license_id character varying(26),
    target_license_id character varying(26),
    shipped_at timestamp without time zone,
    inventory_id character varying(26),
    product_type character varying(256),
    product_name character varying(256),
    variety_name character varying(256),
    unit_price numeric(16,4),
    unit_count numeric(16,4),
    unit_weight numeric(16,4)
);


--
-- Name: inventory; Type: TABLE;
--

CREATE TABLE public.inventory (
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


--
-- Name: product; Type: TABLE;
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


--
-- Name: b2c_sale; Type: TABLE;
--

CREATE TABLE public.b2c_sale (
    id character varying(26) NOT NULL,
    license_id character varying(26) NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone,
    stat integer DEFAULT 200 NOT NULL,
    flag integer DEFAULT 0 NOT NULL,
    full_price numeric(16,4),
    meta jsonb
);


--
-- Name: b2c_sale_item; Type: TABLE;
--

CREATE TABLE public.b2c_sale_item (
    id character varying(26) NOT NULL,
    b2c_sale_id character varying(26) NOT NULL,
    inventory_id character varying(26) NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone,
    stat integer DEFAULT 200 NOT NULL,
    flag integer DEFAULT 0 NOT NULL,
    qty numeric(16,4) NOT NULL,
    unit_price numeric(16,4) NOT NULL,
    package_size numeric(16,4),
    package_unit character varying(8),
    product_type character varying(256),
    meta jsonb
);


--
-- Name: b2c_sale_item_full; Type: TABLE;
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


--
-- Name: company; Type: TABLE;
--

CREATE TABLE public.company (
    id character varying(26) NOT NULL,
    name character varying(256)
);


--
-- Name: contact; Type: TABLE;
--

CREATE TABLE public.contact (
    id character varying(26),
    ulid character varying(26),
    name character varying(256),
    email character varying(256)
);


--
-- Name: lab_report; Type: TABLE;
--

CREATE TABLE public.lab_report (
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


--
-- Name: lab_report_ext; Type: TABLE;
--

CREATE TABLE public.lab_report_ext (
    id character varying(26) NOT NULL,
    sample_id character varying(26)
);


--
-- Name: inventory_lab_report; Type: TABLE;
--

CREATE TABLE public.inventory_lab_report (
    inventory_id character varying(26) NOT NULL,
    lab_report_id character varying(26) NOT NULL,
    type character varying(32)
);


--
-- Name: license; Type: TABLE;
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


--
-- Name: license_contact; Type: TABLE;
--

CREATE TABLE public.license_contact (
    license_id character varying(26) NOT NULL,
    contact_id character varying(26) NOT NULL
);


--
-- Name: license_history; Type: TABLE;
--

CREATE TABLE public.license_history (
    id character varying(26) NOT NULL,
    license_id character varying(26) NOT NULL,
    license_ulid character varying(26) NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    type character varying(32),
    name character varying(256),
    meta jsonb
);


--
-- Name: license_revenue; Type: TABLE;
--

CREATE TABLE public.license_revenue (
    id character varying(26) NOT NULL,
    license_id character varying(26),
    source character varying(8),
    month date,
    rev_amount numeric(17,2),
    tax_amount numeric(17,2)
);


--
-- Name: product_license_name; Type: TABLE; Owner: postgres
--

CREATE TABLE public.product_license_name (
    inventory_count bigint,
    license_id character varying(26),
    name character varying(256)
);


--
-- Name: variety; Type: TABLE;
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


--
-- Name: b2b_path b2b_path_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.b2b_path
    ADD CONSTRAINT b2b_path_pkey PRIMARY KEY (source_license_id, target_license_id);


--
-- Name: b2c_sale_item b2c_sale_item_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.b2c_sale_item
    ADD CONSTRAINT b2c_sale_item_pkey PRIMARY KEY (id);


--
-- Name: b2c_sale b2c_sale_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.b2c_sale
    ADD CONSTRAINT b2c_sale_pkey PRIMARY KEY (id);


--
-- Name: company company_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.company
    ADD CONSTRAINT company_pkey PRIMARY KEY (id);


--
-- Name: lab_report_ext lab_report_ext_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.lab_report_ext
    ADD CONSTRAINT lab_report_ext_pkey PRIMARY KEY (id);


--
-- Name: inventory_lab_report inventory_lab_report_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.inventory_lab_report
    ADD CONSTRAINT inventory_lab_report_pkey PRIMARY KEY (inventory_id, lab_report_id);


--
-- Name: lab_report lab_report_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.lab_report
    ADD CONSTRAINT lab_report_pkey PRIMARY KEY (id);


--
-- Name: license_history license_history_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.license_history
    ADD CONSTRAINT license_history_pkey PRIMARY KEY (id);


--
-- Name: license license_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.license
    ADD CONSTRAINT license_pkey PRIMARY KEY (id);


--
-- Name: license_revenue license_revenue_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.license_revenue
    ADD CONSTRAINT license_revenue_pkey PRIMARY KEY (id);


--
-- Name: inventory inventory_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.inventory
    ADD CONSTRAINT inventory_pkey PRIMARY KEY (id);


--
-- Name: product product_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.product
    ADD CONSTRAINT product_pkey PRIMARY KEY (id);


--
-- Name: variety variety_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.variety
    ADD CONSTRAINT variety_pkey PRIMARY KEY (id);


--
-- Name: b2b_sale_item transfer_item_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.b2b_sale_item
    ADD CONSTRAINT transfer_item_pkey PRIMARY KEY (id);


--
-- Name: b2b_sale transfer_pkey; Type: CONSTRAINT;
--

ALTER TABLE ONLY public.b2b_sale
    ADD CONSTRAINT transfer_pkey PRIMARY KEY (id);


--
-- Name: transfer_item_b2b_sale_id_idx; Type: INDEX;
--

CREATE INDEX transfer_item_b2b_sale_id_idx ON public.b2b_sale_item USING btree (b2b_sale_id);


--
-- Name: license_history license_history_license_id_fkey; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY public.license_history
    ADD CONSTRAINT license_history_license_id_fkey FOREIGN KEY (license_id) REFERENCES public.license(id);


--
-- Name: license_revenue license_revenue_license_id_fkey; Type: FK CONSTRAINT;
--

ALTER TABLE ONLY public.license_revenue
    ADD CONSTRAINT license_revenue_license_id_fkey FOREIGN KEY (license_id) REFERENCES public.license(id);


--
-- PostgreSQL database dump complete
--
