SET statement_timeout = '10800s';
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';
SET default_table_access_method = heap;


-- Extensions --
CREATE EXTENSION IF NOT EXISTS citext;
CREATE EXTENSION IF NOT EXISTS pg_stat_statements;
CREATE EXTENSION IF NOT EXISTS pgcrypto;


-- Types --
CREATE TYPE blacklist_type AS ENUM (
    'email',
    'phone',
    'ip'
);


CREATE TYPE dshb_operators_change_email_status AS ENUM (
    'unused',
    'used',
    'invalidated'
);


CREATE TYPE dshb_operators_forgot_password_status AS ENUM (
    'unused',
    'used',
    'invalidated'
);


CREATE DOMAIN email AS citext
	CONSTRAINT email_check CHECK ((VALUE OPERATOR(~) '^[a-zA-Z0-9.!#$%&''*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$'::citext));


CREATE TYPE queue_account_operation_action AS ENUM (
    'blacklist',
    'delete',
    'calculate_risk_score',
    'enrichment'
);


CREATE TYPE queue_account_operation_status AS ENUM (
    'waiting',
    'executing',
    'completed',
    'failed'
);


CREATE TYPE unreviewed_items_reminder_frequency AS ENUM (
    'daily',
    'weekly',
    'off'
);


-- Functions --
CREATE FUNCTION dshb_api_co_owners_creator_check()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (SELECT 1 FROM dshb_api WHERE creator = NEW.operator)
    THEN
        RAISE EXCEPTION 'A row with operator % already exists in the dshb_api table''s creator column', NEW.operator;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE FUNCTION event_lastseen()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.lastseen < OLD.lastseen THEN
        NEW.lastseen := OLD.lastseen;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE FUNCTION restrict_update()
RETURNS TRIGGER AS $$
BEGIN
   IF NEW.key <> OLD.key THEN
      RAISE EXCEPTION 'not allowed';
   END IF;
   RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE FUNCTION search_whole_db(_like_pattern text)
RETURNS TABLE(_tbl regclass, _ctid tid) AS $$
BEGIN
   FOR _tbl IN
      SELECT c.oid::regclass
      FROM   pg_class c
      JOIN   pg_namespace n ON n.oid = c.relnamespace
      WHERE  c.relkind = 'r'                           -- only tables
      AND    n.nspname !~ '^(pg_|information_schema)'  -- exclude system schemas
      ORDER BY n.nspname, c.relname
   LOOP
      RETURN QUERY EXECUTE format(
         'SELECT $1, ctid FROM %s t WHERE t::text ~~ %L',
         _tbl, '%' || _like_pattern || '%'
      );
   END LOOP;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION queue_new_events_cursor_check()
RETURNS TRIGGER
AS $$
BEGIN
    IF (SELECT COUNT(*) FROM queue_new_events_cursor) > 0 THEN
        RAISE EXCEPTION 'A row in this table already exists';
    ELSE
        RETURN NEW;
    END IF;
END;
$$ LANGUAGE plpgsql;

-- Tables --
CREATE SEQUENCE event_logbook_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_logbook (
    id bigint NOT NULL DEFAULT nextval('event_logbook_id_seq'::regclass),
    ended timestamp(3) without time zone DEFAULT now() NOT NULL,
    key smallint NOT NULL,
    ip INET,
    event bigint,
    error_type smallint NOT NULL,
    error_text text,
    raw text,
    raw_time text,
    started timestamp(3) without time zone
);

ALTER SEQUENCE event_logbook_id_seq OWNED BY event_logbook.id;

CREATE UNIQUE INDEX event_logbook_id_uidx ON event_logbook USING btree (id);
CREATE INDEX event_logbook_ended_idx ON event_logbook USING brin (ended);
CREATE INDEX event_logbook_key_idx ON event_logbook USING btree (key);
CREATE INDEX event_logbook_raw_time_idx ON event_logbook USING btree (raw_time);


-- sequence for user sessions decisions --
CREATE SEQUENCE session_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_session (
    id bigint NOT NULL,
    key smallint NOT NULL,
    account_id bigint NOT NULL,
    total_visit integer DEFAULT 0,
    total_device integer DEFAULT 0,
    total_ip integer DEFAULT 0,
    total_country integer DEFAULT 0,
    lastseen timestamp(3) without time zone NOT NULL,
    created timestamp(3) without time zone NOT NULL,
    updated timestamp without time zone NOT NULL
);

CREATE UNIQUE INDEX event_session_id_uidx ON event_session USING btree (id);
CREATE INDEX event_session_account_id_idx ON event_session USING btree (account_id);
CREATE INDEX event_session_lastseen_idx ON event_session USING btree (lastseen);
CREATE INDEX event_session_lastseen_updated_idx ON event_session(lastseen, updated) WHERE lastseen >= updated;


CREATE TABLE countries (
    id character varying(64) NOT NULL,
    value character varying(64) NOT NULL,
    serial integer NOT NULL
);

CREATE UNIQUE INDEX countries_serial_uidx ON countries USING btree (serial);
CREATE UNIQUE INDEX countries_value_uidx ON countries USING btree (value);

ALTER TABLE ONLY countries ADD CONSTRAINT countries_id_pkey PRIMARY KEY (id);


CREATE TABLE event_type (
    id smallint NOT NULL,
    value text NOT NULL,
    name text NOT NULL
);

ALTER TABLE ONLY event_type ADD CONSTRAINT event_type_id_pkey PRIMARY KEY (id);


CREATE TABLE event_error_type (
    id smallint NOT NULL,
    value text NOT NULL,
    name text NOT NULL
);

ALTER TABLE ONLY event_error_type ADD CONSTRAINT event_error_type_id_pkey PRIMARY KEY (id);


CREATE TABLE event_http_method (
    id smallint NOT NULL,
    value TEXT NOT NULL,
    name TEXT NOT NULL
);

ALTER TABLE ONLY event_http_method ADD CONSTRAINT event_http_method_id_pkey PRIMARY KEY (id);


CREATE TABLE dshb_rules (
    id integer NOT NULL
);

ALTER TABLE ONLY dshb_rules ADD CONSTRAINT dshb_rules_id_pkey PRIMARY KEY (id);


CREATE TABLE dshb_sessions (
    session_id character varying(255) NOT NULL,
    data text,
    ip character varying(45),
    agent character varying(300),
    stamp integer
);

ALTER TABLE ONLY dshb_sessions ADD CONSTRAINT dshb_sessions_session_id_pkey PRIMARY KEY (session_id);


CREATE TABLE dshb_api_co_owners (
    operator BIGINT NOT NULL,
    api BIGINT NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);

ALTER TABLE ONLY dshb_api_co_owners ADD CONSTRAINT dshb_api_co_owners_operator_key UNIQUE (operator);
ALTER TABLE ONLY dshb_api_co_owners ADD CONSTRAINT dshb_api_co_owners_operator_api_pkey PRIMARY KEY (operator, api);


CREATE SEQUENCE event_account_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_account (
    id BIGINT NOT NULL DEFAULT nextval('event_account_id_seq'::regclass),
    userid character varying(100) NOT NULL,
    created timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    key int,
    lastip inet,
    lastseen timestamp without time zone NOT NULL,
    fullname text,
    is_important boolean DEFAULT false NOT NULL,
    firstname character varying(100),
    middlename character varying(100),
    lastname character varying(100),
    total_visit integer DEFAULT 0,
    total_country integer DEFAULT 0,
    total_ip integer DEFAULT 0,
    total_device integer DEFAULT 0,
    score_updated_at timestamp without time zone,
    score integer,
    score_details text,
    lastemail integer,
    lastphone integer,
    total_shared_ip integer DEFAULT 0,
    total_shared_phone integer DEFAULT 0,
    reviewed boolean DEFAULT false,
    fraud boolean,
    latest_decision timestamp without time zone,
    score_recalculate boolean DEFAULT true,
    session_id bigint,
    updated timestamp without time zone NOT NULL
);

ALTER SEQUENCE event_account_id_seq OWNED BY event_account.id;

CREATE UNIQUE INDEX event_account_id_uidx ON event_account USING btree (id);
CREATE UNIQUE INDEX event_account_id_key_uidx ON event_account USING btree (id, key);
CREATE INDEX event_account_lastseen_idx ON event_account USING btree (lastseen);
CREATE INDEX event_account_userid_idx ON event_account USING btree (userid);
CREATE INDEX event_account_key_idx ON event_account USING btree (key);
CREATE INDEX event_account_lastseen_updated_idx ON event_account(lastseen, updated) WHERE lastseen >= updated;

ALTER TABLE ONLY event_account ADD CONSTRAINT event_account_id_pkey PRIMARY KEY (id);
ALTER TABLE ONLY event_account ADD CONSTRAINT event_account_userid_key_key UNIQUE (userid, key);


CREATE SEQUENCE dshb_api_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- use int for `key` everywhere
CREATE TABLE dshb_api (
    id int NOT NULL DEFAULT nextval('dshb_api_id_seq'::regclass),
    key text NOT NULL,
    quote integer NOT NULL,
    creator BIGINT NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    skip_enriching_attributes jsonb DEFAULT '[]'::jsonb NOT NULL,
    retention_policy smallint DEFAULT 0 NOT NULL,
    skip_blacklist_sync boolean DEFAULT true NOT NULL,
    token character varying,
    last_call_reached boolean
);

ALTER SEQUENCE dshb_api_id_seq OWNED BY dshb_api.id;

CREATE UNIQUE INDEX dshb_api_key_uidx ON dshb_api USING btree (key);

ALTER TABLE ONLY dshb_api ADD CONSTRAINT dshb_api_key_key UNIQUE (key);
ALTER TABLE ONLY dshb_api ADD CONSTRAINT dshb_api_id_pkey PRIMARY KEY (id);


CREATE SEQUENCE event_device_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_device (
    id BIGINT NOT NULL DEFAULT nextval('event_device_id_seq'::regclass),
    account_id bigint NOT NULL,
    key smallint NOT NULL,
    created timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    lastseen timestamp without time zone DEFAULT '1970-01-01 00:00:00'::timestamp without time zone NOT NULL,
    updated timestamp without time zone NOT NULL,
    user_agent bigint NOT NULL,
    lang text,
    total_visit integer DEFAULT 0
);

ALTER SEQUENCE event_device_id_seq OWNED BY event_device.id;

CREATE INDEX event_device_key_idx ON event_device USING btree (key);
CREATE INDEX event_device_account_id_idx ON event_device USING btree (account_id);
CREATE INDEX event_device_lastseen_idx ON event_device USING btree (lastseen);
CREATE INDEX event_device_user_agent_idx ON event_device USING btree (user_agent);
CREATE INDEX event_device_lastseen_updated_idx ON event_device(lastseen, updated) WHERE lastseen >= updated;

ALTER TABLE ONLY event_device ADD CONSTRAINT event_device_id_pkey PRIMARY KEY (id);
ALTER TABLE ONLY event_device ADD CONSTRAINT event_device_account_id_key_user_agent_key UNIQUE (account_id, key, user_agent);


CREATE SEQUENCE dshb_logs_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE dshb_logs (
    id BIGINT NOT NULL DEFAULT nextval('dshb_logs_id_seq'::regclass),
    text text NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);

ALTER SEQUENCE dshb_logs_id_seq OWNED BY dshb_logs.id;

ALTER TABLE ONLY dshb_logs ADD CONSTRAINT dshb_logs_id_pkey PRIMARY KEY (id);


CREATE SEQUENCE dshb_manual_check_history_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE dshb_manual_check_history (
    id BIGINT NOT NULL DEFAULT nextval('dshb_manual_check_history_id_seq'::regclass),
    operator BIGINT NOT NULL,
    type text NOT NULL,
    search_query text NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);

ALTER SEQUENCE dshb_manual_check_history_id_seq OWNED BY dshb_manual_check_history.id;

ALTER TABLE ONLY dshb_manual_check_history ADD CONSTRAINT dshb_manual_check_history_id_pkey PRIMARY KEY (id);


CREATE SEQUENCE dshb_message_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE dshb_message (
    id BIGINT NOT NULL DEFAULT nextval('dshb_message_id_seq'::regclass),
    text text,
    title text,
    created_at timestamp without time zone DEFAULT now()
);

ALTER SEQUENCE dshb_message_id_seq OWNED BY dshb_message.id;


CREATE SEQUENCE dshb_operators_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE dshb_operators (
    id BIGINT NOT NULL DEFAULT nextval('dshb_operators_id_seq'::regclass),
    email citext NOT NULL,
    password text,
    firstname text,
    lastname text,
    city text,
    state text,
    zip text,
    country text,
    company text,
    vat text,
    is_active smallint DEFAULT 0,
    activation_key text,
    created_at timestamp with time zone DEFAULT now(),
    street text,
    is_closed smallint DEFAULT 0,
    timezone text DEFAULT 'UTC'::text NOT NULL,
    review_queue_cnt smallint,
    review_queue_updated_at timestamp without time zone,
    last_event_time timestamp without time zone,
    unreviewed_items_reminder_freq unreviewed_items_reminder_frequency DEFAULT 'weekly'::unreviewed_items_reminder_frequency NOT NULL,
    last_unreviewed_items_reminder timestamp without time zone
);

ALTER SEQUENCE dshb_operators_id_seq OWNED BY dshb_operators.id;

CREATE UNIQUE INDEX dshb_operators_activation_key_uidx ON dshb_operators USING btree (activation_key);
CREATE UNIQUE INDEX dshb_operators_email_uidx ON dshb_operators USING btree (email);

ALTER TABLE ONLY dshb_operators ADD CONSTRAINT dshb_operators_id_pkey PRIMARY KEY (id);


CREATE SEQUENCE dshb_operators_change_email_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE dshb_operators_change_email (
    id BIGINT NOT NULL DEFAULT nextval('dshb_operators_change_email_id_seq'::regclass),
    operator_id BIGINT NOT NULL,
    renew_key text NOT NULL,
    status dshb_operators_change_email_status DEFAULT 'unused'::dshb_operators_change_email_status NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    email text NOT NULL
);

ALTER SEQUENCE dshb_operators_change_email_id_seq OWNED BY dshb_operators_change_email.id;

CREATE INDEX dshb_operators_change_email_operator_id_status_idx ON dshb_operators_change_email USING btree (operator_id, status);

ALTER TABLE ONLY dshb_operators_change_email ADD CONSTRAINT dshb_operators_change_email_id_pkey PRIMARY KEY (id);
ALTER TABLE ONLY dshb_operators_change_email ADD CONSTRAINT dshb_operators_change_email_renew_key_key UNIQUE (renew_key);


CREATE SEQUENCE dshb_operators_forgot_password_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE dshb_operators_forgot_password (
    id BIGINT NOT NULL DEFAULT nextval('dshb_operators_forgot_password_id_seq'::regclass),
    operator_id BIGINT NOT NULL,
    renew_key text NOT NULL,
    status dshb_operators_forgot_password_status DEFAULT 'unused'::dshb_operators_forgot_password_status NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);

ALTER SEQUENCE dshb_operators_forgot_password_id_seq OWNED BY dshb_operators_forgot_password.id;

CREATE INDEX dshb_operators_forgot_password_operator_id_status_idx ON dshb_operators_forgot_password USING btree (operator_id, status);

ALTER TABLE ONLY dshb_operators_forgot_password ADD CONSTRAINT dshb_operators_forgot_password_pkey PRIMARY KEY (id);
ALTER TABLE ONLY dshb_operators_forgot_password ADD CONSTRAINT dshb_operators_forgot_password_renew_key_key UNIQUE (renew_key);


CREATE SEQUENCE dshb_operators_rules_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE dshb_operators_rules (
    id BIGINT NOT NULL DEFAULT nextval('dshb_operators_rules_id_seq'::regclass),
    rule_id integer,
    value integer DEFAULT 0,
    created_at timestamp with time zone DEFAULT now(),
    key integer,
    proportion real,
    proportion_updated_at timestamp without time zone
);

ALTER SEQUENCE dshb_operators_rules_id_seq OWNED BY dshb_operators_rules.id;

ALTER TABLE ONLY dshb_operators_rules ADD CONSTRAINT dshb_operators_rules_id_pkey PRIMARY KEY (id);


CREATE SEQUENCE event_email_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_email (
    id BIGINT NOT NULL DEFAULT nextval('event_email_id_seq'::regclass),
    account_id BIGINT NOT NULL,
    email email,
    lastseen timestamp without time zone NOT NULL,
    created timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    key integer DEFAULT 0 NOT NULL,
    checked boolean,
    data_breach boolean,
    profiles integer,
    blockemails boolean,
    domain_contact_email boolean,
    domain BIGINT,
    fraud_detected boolean DEFAULT false NOT NULL,
    hash text,
    alert_list boolean,
    data_breaches integer,
    earliest_breach text
);

ALTER SEQUENCE event_email_id_seq OWNED BY event_email.id;

CREATE INDEX event_email_email_idx ON event_email USING btree (email);
CREATE INDEX event_email_key_idx ON event_email USING btree (key);
CREATE INDEX event_email_lastseen_idx ON event_email USING btree (lastseen);
CREATE INDEX event_email_domain_idx ON event_email USING btree (domain);
CREATE INDEX event_email_account_id_idx ON event_email USING btree (account_id);

ALTER TABLE ONLY event_email ADD CONSTRAINT event_email_id_pkey PRIMARY KEY (id);
ALTER TABLE ONLY event_email ADD CONSTRAINT event_email_account_id_email_key UNIQUE (account_id, email);


CREATE SEQUENCE event_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event (
    id bigint NOT NULL DEFAULT nextval('event_id_seq'::regclass),
    key smallint NOT NULL,
    account bigint NOT NULL,
    ip bigint NOT NULL,
    url bigint NOT NULL,
    device bigint NOT NULL,
    "time" timestamp(3) without time zone NOT NULL,
    query bigint,
    payload json,
    traceid character varying(36) DEFAULT NULL::character varying,
    referer bigint,
    type smallint DEFAULT 1 NOT NULL,
    email integer,
    phone integer,
    http_code smallint,
    http_method smallint,
    session_id bigint NOT NULL
);

ALTER SEQUENCE event_id_seq OWNED BY event.id;

CREATE UNIQUE INDEX event_id_uidx ON event USING btree (id);
CREATE INDEX event_device_idx ON event USING btree (device);
CREATE INDEX event_account_idx ON event USING btree (account);
CREATE INDEX event_referer_idx ON event USING btree (referer);
CREATE INDEX event_time_idx ON event USING brin ("time");
CREATE INDEX event_url_idx ON event USING btree (url);
CREATE INDEX event_key_idx ON event USING btree (key);
CREATE INDEX event_query_idx ON event USING btree (query);
CREATE INDEX event_type_idx ON event USING btree (type);
CREATE INDEX event_ip_idx ON event USING btree (ip);
CREATE INDEX event_email_idx ON event USING btree (email);
CREATE INDEX event_session_id_idx ON event USING btree (session_id);
CREATE INDEX event_key_ip_idx ON event (ip, key);
CREATE INDEX event_ip_account_key_idx ON event (ip, account, key);


CREATE SEQUENCE event_country_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_country (
    id BIGINT NOT NULL DEFAULT nextval('event_country_id_seq'::regclass),
    key integer NOT NULL,
    country integer NOT NULL,
    total_visit integer DEFAULT 0,
    total_ip integer DEFAULT 0,
    total_account integer DEFAULT 0,
    lastseen timestamp without time zone,
    created timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated timestamp without time zone NOT NULL
);

ALTER SEQUENCE event_country_id_seq OWNED BY event_country.id;

ALTER TABLE ONLY event_country ADD CONSTRAINT event_country_id_pkey PRIMARY KEY (id);
ALTER TABLE ONLY event_country ADD CONSTRAINT event_country_country_key_key UNIQUE (country, key);
CREATE INDEX event_country_lastseen_updated_idx ON event_country(lastseen, updated) WHERE lastseen >= updated;


CREATE SEQUENCE event_domain_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_domain (
    id BIGINT NOT NULL DEFAULT nextval('event_domain_id_seq'::regclass),
    key smallint NOT NULL,
    domain text,
    ip inet,
    geo_ip character varying(9) DEFAULT NULL::character varying,
    geo_html character varying(9) DEFAULT NULL::character varying,
    web_server character varying(36) DEFAULT NULL::character varying,
    hostname text DEFAULT NULL::character varying,
    emails text,
    phone character varying(19) DEFAULT NULL::character varying,
    discovery_date timestamp without time zone,
    blockdomains boolean,
    disposable_domains boolean,
    total_visit integer DEFAULT 0,
    total_account integer DEFAULT 0,
    lastseen timestamp without time zone NOT NULL,
    created timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated timestamp without time zone NOT NULL,
    free_email_provider boolean,
    tranco_rank integer,
    creation_date timestamp without time zone,
    expiration_date timestamp without time zone,
    return_code integer,
    closest_snapshot text,
    checked boolean,
    mx_record boolean,
    disabled boolean DEFAULT NULL
);

ALTER SEQUENCE event_domain_id_seq OWNED BY event_domain.id;

ALTER TABLE ONLY event_domain ADD CONSTRAINT event_domain_key_domain_key UNIQUE (key, domain);
ALTER TABLE ONLY event_domain ADD CONSTRAINT event_domain_id_pkey PRIMARY KEY (id);
CREATE INDEX event_domain_lastseen_updated_idx ON event_domain(lastseen, updated) WHERE lastseen >= updated;


CREATE SEQUENCE event_incorrect_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_incorrect (
    id bigint NOT NULL DEFAULT nextval('event_incorrect_id_seq'::regclass),
    payload json NOT NULL,
    created timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    errors json,
    traceid character varying(36) DEFAULT NULL::character varying,
    key integer
);

ALTER SEQUENCE event_incorrect_id_seq OWNED BY event_incorrect.id;

ALTER TABLE ONLY event_incorrect ADD CONSTRAINT event_incorrect_id_pkey PRIMARY KEY (id);


CREATE SEQUENCE event_ip_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_ip (
    id bigint NOT NULL DEFAULT nextval('event_ip_id_seq'::regclass),
    ip inet NOT NULL,
    key smallint NOT NULL,
    country smallint NOT NULL,
    cidr text,
    data_center boolean,
    tor boolean,
    vpn boolean,
    checked boolean DEFAULT false,
    relay boolean,
    lastseen timestamp without time zone DEFAULT '1970-01-01 00:00:00'::timestamp without time zone NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    updated timestamp without time zone NOT NULL,
    lastcheck timestamp without time zone,
    total_visit integer DEFAULT 0,
    blocklist boolean,
    isp bigint,
    shared smallint DEFAULT 0,
    domains_count json,
    fraud_detected boolean DEFAULT false NOT NULL,
    hash text,
    alert_list boolean,
    starlink boolean
);

ALTER SEQUENCE event_ip_id_seq OWNED BY event_ip.id;

CREATE INDEX event_ip_checked_idx ON event_ip USING btree (checked);
CREATE INDEX event_ip_country_idx ON event_ip USING btree (country);
CREATE INDEX event_ip_ip_idx ON event_ip USING gist (ip inet_ops);
CREATE INDEX event_ip_key_idx ON event_ip USING btree (key);
CREATE INDEX event_ip_lastseen_idx ON event_ip USING btree (lastseen);
CREATE INDEX event_ip_isp_idx ON event_ip USING btree (isp);
CREATE INDEX event_ip_country_key_idx ON event_ip (country, key);
CREATE INDEX event_ip_key_isp_idx ON event_ip (key, isp);
CREATE INDEX event_ip_lastseen_updated_idx ON event_ip(lastseen, updated) WHERE lastseen >= updated;

ALTER TABLE ONLY event_ip ADD CONSTRAINT event_ip_key_ip UNIQUE (key, ip);
ALTER TABLE ONLY event_ip ADD CONSTRAINT event_ip_id_pkey PRIMARY KEY (id);


CREATE SEQUENCE event_isp_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_isp (
    id BIGINT NOT NULL DEFAULT nextval('event_isp_id_seq'::regclass),
    key smallint NOT NULL,
    asn integer NOT NULL,
    name text,
    description text,
    total_visit integer DEFAULT 0,
    total_account integer DEFAULT 0,
    lastseen timestamp without time zone,
    created timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated timestamp without time zone NOT NULL
);

ALTER SEQUENCE event_isp_id_seq OWNED BY event_isp.id;

CREATE INDEX event_isp_key_id ON event_isp USING btree (key);

ALTER TABLE ONLY event_isp ADD CONSTRAINT event_isp_key_asn_key UNIQUE (key, asn);
ALTER TABLE ONLY event_isp ADD CONSTRAINT event_isp_id_pkey PRIMARY KEY (id);
CREATE INDEX event_isp_asn_ids ON event_isp(asn);
CREATE INDEX event_isp_lastseen_updated_idx ON event_isp(lastseen, updated) WHERE lastseen >= updated;


CREATE SEQUENCE event_phone_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_phone (
    id bigint NOT NULL DEFAULT nextval('event_phone_id_seq'::regclass),
    account_id BIGINT NOT NULL,
    key integer NOT NULL,
    phone_number character varying(19) NOT NULL,
    calling_country_code integer,
    national_format character varying DEFAULT NULL::character varying,
    country_code smallint,
    validation_errors json,
    mobile_country_code smallint,
    mobile_network_code smallint,
    carrier_name character varying(128) DEFAULT NULL::character varying,
    type character varying(32) DEFAULT NULL::character varying,
    lastseen timestamp without time zone NOT NULL,
    created timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated timestamp without time zone NOT NULL,
    checked boolean,
    shared smallint DEFAULT 0,
    fraud_detected boolean DEFAULT false NOT NULL,
    hash text,
    alert_list boolean,
    profiles integer,
    iso_country_code character varying(8),
    invalid boolean
);

ALTER SEQUENCE event_phone_id_seq OWNED BY event_phone.id;

CREATE INDEX event_phone_account_id_idx ON event_phone USING btree (account_id);
CREATE INDEX event_phone_lastseen_updated_idx ON event_phone(lastseen, updated) WHERE lastseen >= updated;

ALTER TABLE ONLY event_phone ADD CONSTRAINT event_phone_key_account_id_phone_number_key UNIQUE (key, account_id, phone_number);
ALTER TABLE ONLY event_phone ADD CONSTRAINT event_phone_id_pkey PRIMARY KEY (id);


CREATE SEQUENCE event_referer_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_referer (
    id bigint NOT NULL DEFAULT nextval('event_referer_id_seq'::regclass),
    key smallint NOT NULL,
    referer text,
    lastseen timestamp without time zone NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL
);

ALTER SEQUENCE event_referer_id_seq OWNED BY event_referer.id;

CREATE INDEX event_referer_key_idx ON event_referer USING btree (key);

ALTER TABLE ONLY event_referer ADD CONSTRAINT event_referer_id_pkey PRIMARY KEY (id);
ALTER TABLE ONLY event_referer ADD CONSTRAINT event_referer_referer_key_key UNIQUE (referer, key);



CREATE SEQUENCE event_ua_parsed_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_ua_parsed (
    id BIGINT NOT NULL DEFAULT nextval('event_ua_parsed_id_seq'::regclass),
    device text,
    browser_name text,
    browser_version text,
    os_name text,
    os_version text,
    ua text,
    uuid uuid,
    modified boolean,
    checked boolean DEFAULT false NOT NULL,
    key smallint NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL
);

ALTER SEQUENCE event_ua_parsed_id_seq OWNED BY event_ua_parsed.id;

ALTER TABLE ONLY event_ua_parsed ADD CONSTRAINT event_ua_parsed_ua_key_key UNIQUE (ua, key);
ALTER TABLE ONLY event_ua_parsed ADD CONSTRAINT event_ua_parsed_id_pkey PRIMARY KEY (id);


CREATE SEQUENCE event_url_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_url (
    id BIGINT NOT NULL DEFAULT nextval('event_url_id_seq'::regclass),
    key smallint NOT NULL,
    url text,
    lastseen timestamp without time zone DEFAULT '1970-01-01 00:00:00'::timestamp without time zone NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    updated timestamp without time zone NOT NULL,
    title text,
    total_visit integer DEFAULT 0,
    total_ip integer DEFAULT 0,
    total_device integer DEFAULT 0,
    total_account integer DEFAULT 0,
    total_country integer DEFAULT 0,
    http_code smallint
);

ALTER SEQUENCE event_url_id_seq OWNED BY event_url.id;

CREATE INDEX event_url_key_idx ON event_url USING btree (key);
CREATE INDEX event_url_url_idx ON event_url USING btree (url);
CREATE INDEX event_url_lastseen_idx ON event_url USING btree (lastseen);
CREATE INDEX event_url_lastseen_updated_idx ON event_url(lastseen, updated) WHERE lastseen >= updated;


ALTER TABLE ONLY event_url ADD CONSTRAINT event_url_id_pkey PRIMARY KEY (id);
ALTER TABLE ONLY event_url ADD CONSTRAINT event_url_url_key_key UNIQUE (url, key);


CREATE SEQUENCE event_url_query_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE event_url_query (
    id BIGINT NOT NULL DEFAULT nextval('event_url_id_seq'::regclass),
    key smallint NOT NULL,
    url integer NOT NULL,
    query text,
    lastseen timestamp without time zone NOT NULL,
    created timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

ALTER SEQUENCE event_url_query_id_seq OWNED BY event_url_query.id;

CREATE INDEX event_url_query_url_idx ON event_url_query USING btree (url);
CREATE INDEX event_url_query_key_idx ON event_url_query USING btree (key);

ALTER TABLE ONLY event_url_query ADD CONSTRAINT event_url_query_id_pkey PRIMARY KEY (id);
ALTER TABLE ONLY event_url_query ADD CONSTRAINT event_url_query_key_url_query_key UNIQUE (key, url, query);


CREATE SEQUENCE migrations_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE migrations (
    id BIGINT NOT NULL DEFAULT nextval('migrations_id_seq'::regclass),
    name character varying(255) NOT NULL,
    created_at timestamp without time zone NOT NULL
);

ALTER SEQUENCE migrations_id_seq OWNED BY migrations.id;

ALTER TABLE ONLY migrations ADD CONSTRAINT migrations_id_pkey PRIMARY KEY (id);


CREATE SEQUENCE queue_account_operation_id_seq
    AS BIGINT
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

CREATE TABLE queue_account_operation (
    id BIGINT NOT NULL DEFAULT nextval('queue_account_operation_id_seq'::regclass),
    created timestamp without time zone DEFAULT now() NOT NULL,
    updated timestamp without time zone DEFAULT now() NOT NULL,
    event_account bigint,
    key smallint NOT NULL,
    action queue_account_operation_action NOT NULL,
    status queue_account_operation_status DEFAULT 'waiting'::queue_account_operation_status NOT NULL
);

ALTER SEQUENCE queue_account_operation_id_seq OWNED BY queue_account_operation.id;

CREATE INDEX queue_account_operation_event_account_key_action_idx ON queue_account_operation USING btree (event_account, key, action);
CREATE INDEX queue_account_operation_status_updated_idx ON queue_account_operation USING btree (status, updated);

ALTER TABLE ONLY queue_account_operation ADD CONSTRAINT queue_account_operation_id_pkey PRIMARY KEY (id);


CREATE TABLE queue_new_events_cursor (
    last_event_id bigint NOT NULL,
    locked boolean NOT NULL,
    updated timestamp without time zone DEFAULT now()
);


-- Triggers --
CREATE TRIGGER dshb_api_co_owners_creator_check BEFORE INSERT OR UPDATE ON dshb_api_co_owners FOR EACH ROW EXECUTE FUNCTION dshb_api_co_owners_creator_check();

CREATE TRIGGER emp_stamp BEFORE UPDATE ON event_account FOR EACH ROW EXECUTE FUNCTION event_lastseen();
CREATE TRIGGER emp_stamp BEFORE UPDATE ON event_country FOR EACH ROW EXECUTE FUNCTION event_lastseen();
CREATE TRIGGER emp_stamp BEFORE UPDATE ON event_device FOR EACH ROW EXECUTE FUNCTION event_lastseen();
CREATE TRIGGER emp_stamp BEFORE UPDATE ON event_ip FOR EACH ROW EXECUTE FUNCTION event_lastseen();
CREATE TRIGGER emp_stamp BEFORE UPDATE ON event_referer FOR EACH ROW EXECUTE FUNCTION event_lastseen();
CREATE TRIGGER emp_stamp BEFORE UPDATE ON event_url FOR EACH ROW EXECUTE FUNCTION event_lastseen();
CREATE TRIGGER emp_stamp BEFORE UPDATE ON event_url_query FOR EACH ROW EXECUTE FUNCTION event_lastseen();
CREATE TRIGGER emp_stamp BEFORE UPDATE ON event_session FOR EACH ROW EXECUTE FUNCTION event_lastseen();

CREATE TRIGGER queue_new_events_cursor_trigger BEFORE INSERT ON queue_new_events_cursor FOR EACH ROW EXECUTE FUNCTION queue_new_events_cursor_check();

CREATE TRIGGER restrict_update BEFORE UPDATE ON dshb_operators_rules FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_account FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_country FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_device FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_domain FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_email FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_ip FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_isp FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_phone FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_referer FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_ua_parsed FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_url FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_url_query FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_session FOR EACH ROW EXECUTE FUNCTION restrict_update();
CREATE TRIGGER restrict_update BEFORE UPDATE ON event_logbook FOR EACH ROW EXECUTE FUNCTION restrict_update();

-- Constraints --
ALTER TABLE ONLY dshb_api ADD CONSTRAINT dshb_api_creator_fkey FOREIGN KEY (creator) REFERENCES dshb_operators(id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY dshb_manual_check_history ADD CONSTRAINT dshb_manual_check_history_operator_fkey FOREIGN KEY (operator) REFERENCES dshb_operators(id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY dshb_operators_change_email ADD CONSTRAINT dshb_operators_change_email_operator_id_fkey FOREIGN KEY (operator_id) REFERENCES dshb_operators(id) ON DELETE CASCADE;

ALTER TABLE ONLY dshb_operators_forgot_password ADD CONSTRAINT dshb_operators_forgot_password_operator_id_fkey FOREIGN KEY (operator_id) REFERENCES dshb_operators(id) ON DELETE CASCADE;

ALTER TABLE ONLY dshb_operators_rules ADD CONSTRAINT dshb_operators_rules_rule_id_fkey FOREIGN KEY (rule_id) REFERENCES dshb_rules(id) ON DELETE CASCADE;

ALTER TABLE ONLY event_domain ADD CONSTRAINT event_domain_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE;

ALTER TABLE ONLY event_country ADD CONSTRAINT event_country_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE;

ALTER TABLE ONLY event_phone ADD CONSTRAINT event_phone_account_id_key_fkey FOREIGN KEY (account_id, key) REFERENCES event_account(id, key) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY event_isp ADD CONSTRAINT event_isp_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE;

ALTER TABLE ONLY event_referer ADD CONSTRAINT event_referer_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE;

ALTER TABLE ONLY event_ua_parsed ADD CONSTRAINT event_ua_parsed_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE;

ALTER TABLE ONLY event_url ADD CONSTRAINT event_url_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE;

ALTER TABLE ONLY event_account ADD CONSTRAINT event_account_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE;

ALTER TABLE ONLY event_session ADD CONSTRAINT event_session_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE;
ALTER TABLE ONLY event_session ADD CONSTRAINT event_session_id_key_fkey FOREIGN KEY (account_id, key) REFERENCES event_account(id, key) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY event_device ADD CONSTRAINT event_device_account_id_key_fkey FOREIGN KEY (account_id, key) REFERENCES event_account(id, key) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY event_device ADD CONSTRAINT event_device_user_agent_fkey FOREIGN KEY (user_agent) REFERENCES event_ua_parsed(id);

ALTER TABLE ONLY queue_account_operation ADD CONSTRAINT queue_account_operation_event_account_fkey FOREIGN KEY (event_account) REFERENCES event_account(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY queue_account_operation ADD CONSTRAINT queue_account_operation_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY dshb_api_co_owners ADD CONSTRAINT dshb_api_co_owners_api_fkey FOREIGN KEY (api) REFERENCES dshb_api(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY dshb_api_co_owners ADD CONSTRAINT dshb_api_co_owners_operator_fkey FOREIGN KEY (operator) REFERENCES dshb_operators(id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY event_email ADD CONSTRAINT event_email_domain_fkey FOREIGN KEY (domain) REFERENCES event_domain(id);
ALTER TABLE ONLY event_email ADD CONSTRAINT event_email_account_id_key_fkey FOREIGN KEY (account_id, key) REFERENCES event_account(id, key) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY event_ip ADD CONSTRAINT event_ip_isp_fkey FOREIGN KEY (isp) REFERENCES event_isp(id);
ALTER TABLE ONLY event_ip ADD CONSTRAINT event_ip_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE;

ALTER TABLE ONLY event_url_query ADD CONSTRAINT event_url_query_url_fkey FOREIGN KEY (url) REFERENCES event_url(id);
ALTER TABLE ONLY event_url_query ADD CONSTRAINT event_url_query_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE;

ALTER TABLE ONLY event_logbook ADD CONSTRAINT event_logbook_error_type_fkey FOREIGN KEY (error_type) REFERENCES event_error_type(id);
ALTER TABLE ONLY event_logbook ADD CONSTRAINT event_logbook_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE;

ALTER TABLE ONLY event ADD CONSTRAINT event_referer_fkey FOREIGN KEY (referer) REFERENCES event_referer(id);
ALTER TABLE ONLY event ADD CONSTRAINT event_type_fkey FOREIGN KEY (type) REFERENCES event_type(id);
ALTER TABLE ONLY event ADD CONSTRAINT event_http_method_fkey FOREIGN KEY (http_method) REFERENCES event_http_method(id);
ALTER TABLE ONLY event ADD CONSTRAINT event_account_fkey FOREIGN KEY (account) REFERENCES event_account(id);
ALTER TABLE ONLY event ADD CONSTRAINT event_ip_fkey FOREIGN KEY (ip) REFERENCES event_ip(id);
ALTER TABLE ONLY event ADD CONSTRAINT event_device_fkey FOREIGN KEY (device) REFERENCES event_device(id);
ALTER TABLE ONLY event ADD CONSTRAINT event_url_fkey FOREIGN KEY (url) REFERENCES event_url(id);
ALTER TABLE ONLY event ADD CONSTRAINT event_query_fkey FOREIGN KEY (query) REFERENCES event_url_query(id);
ALTER TABLE ONLY event ADD CONSTRAINT event_session_id_fkey FOREIGN KEY (session_id) REFERENCES event_session(id);
ALTER TABLE ONLY event ADD CONSTRAINT event_email_fkey FOREIGN KEY (email) REFERENCES event_email(id);
ALTER TABLE ONLY event ADD CONSTRAINT event_phone_fkey FOREIGN KEY (phone) REFERENCES event_phone(id);
ALTER TABLE ONLY event ADD CONSTRAINT event_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE;

-- INSERTS --
INSERT INTO queue_new_events_cursor (last_event_id, locked) VALUES (0, false);

INSERT INTO event_type (id, value, name) VALUES
    (1, 'page_view', 'Page View'),
    (2, 'page_edit', 'Page Edit'),
    (3, 'page_delete', 'Page Delete'),
    (4, 'page_search', 'Page Search'),
    (5, 'account_login', 'Login'),
    (6, 'account_logout', 'Logout'),
    (7, 'account_login_fail', 'Login Fail'),
    (8, 'account_registration', 'Registration'),
    (9, 'account_email_change', 'Email Change'),
    (10, 'account_password_change', 'Password Change'),
    (11, 'account_edit', 'Account Edit');

INSERT INTO event_http_method (id, value, name) VALUES
    (1, 'get', 'GET'),
    (2, 'post', 'POST'),
    (3, 'head', 'HEAD'),
    (4, 'put', 'PUT'),
    (5, 'delete', 'DELETE'),
    (6, 'patch', 'PATCH'),
    (7, 'trace', 'TRACE'),
    (8, 'connect', 'CONNECT'),
    (9, 'options', 'OPTIONS'),
    (10, 'link', 'LINK'),
    (11, 'unlink', 'UNLINK');

INSERT INTO event_error_type (id, value, name) VALUES
    (0, 'success', 'Successful'),
    (1, 'validation_error', 'Successful with warnings'),
    (2, 'critical_validation_error', 'Request failed'),
    (3, 'critical_error', 'Request failed');

INSERT INTO dshb_rules (id) VALUES (1), (2), (3), (4), (5), (6), (7), (8), (9), (10),
    (11), (12), (13), (14), (15), (16), (17), (18), (19), (20), (21), (22), (23), (24), (25), (26),
    (27), (28), (29), (30), (31), (32), (33), (34), (35), (36), (37), (38), (39), (40), (41), (42),
    (43), (44), (45), (46), (47), (48), (49), (50), (51), (52), (53), (54), (55), (56), (57), (58),
    (59), (60), (61), (62), (63), (64), (65), (66), (67), (68), (69), (70), (71), (72), (73), (74),
    (75), (76), (77), (78), (79), (80), (81), (82), (83), (84), (85), (86), (87), (88), (89), (90),
    (91), (92), (93), (94), (95), (96), (97), (98), (99), (100), (101), (102), (103), (104), (105),
    (106), (107), (108);

INSERT INTO countries (id, value, serial) VALUES ('AD', 'Andorra', 6), ('AE', 'United Arab Emirates', 236),
	('AF', 'Afghanistan', 1), ('AG', 'Antigua & Barbuda', 10), ('AI', 'Anguilla', 8), ('AL', 'Albania', 3),
	('AM', 'Armenia', 12), ('AN', 'Netherlands Antilles', 252), ('AO', 'Angola', 7), ('AP', 'Asia/Pacific Region', 250),
	('AQ', 'Antarctica', 9), ('AR', 'Argentina', 11), ('AS', 'American Samoa', 5), ('AT', 'Austria', 15),
	('AU', 'Australia', 14), ('AW', 'Aruba', 13), ('AX', 'Åland Islands', 2), ('AZ', 'Azerbaijan', 16),
	('BA', 'Bosnia & Herzegovina', 28), ('BB', 'Barbados', 20), ('BD', 'Bangladesh', 19), ('BE', 'Belgium', 22),
	('BF', 'Burkina Faso', 36), ('BG', 'Bulgaria', 35), ('BH', 'Bahrain', 18), ('BI', 'Burundi', 37),
	('BJ', 'Benin', 24), ('BL', 'St. Barthélemy', 205), ('BM', 'Bermuda', 25), ('BN', 'Brunei', 34),
	('BO', 'Bolivia', 27), ('BQ', 'Caribbean Netherlands', 42), ('BR', 'Brazil', 31), ('BS', 'Bahamas', 17),
	('BT', 'Bhutan', 26), ('BV', 'Bouvet Island', 30), ('BW', 'Botswana', 29), ('BY', 'Belarus', 21),
	('BZ', 'Belize', 23), ('CA', 'Canada', 40), ('CC', 'Cocos (Keeling) Islands', 49), ('CD', 'Congo - Kinshasa', 53),
	('CF', 'Central African Republic', 44), ('CG', 'Congo - Brazzaville', 52), ('CH', 'Switzerland', 216), ('CI', 'Côte d’Ivoire', 56),
	('CK', 'Cook Islands', 54), ('CL', 'Chile', 46), ('CM', 'Cameroon', 39), ('CN', 'China', 47),
	('CO', 'Colombia', 50), ('CR', 'Costa Rica', 55), ('CS', 'Czechoslovakia', 253), ('CU', 'Cuba', 58),
	('CV', 'Cape Verde', 41), ('CW', 'Curaçao', 59), ('CX', 'Christmas Island', 48), ('CY', 'Cyprus', 60),
	('CZ', 'Czechia', 61), ('DE', 'Germany', 85), ('DJ', 'Djibouti', 63), ('DK', 'Denmark', 62),
	('DM', 'Dominica', 64), ('DO', 'Dominican Republic', 65), ('DZ', 'Algeria', 4), ('EC', 'Ecuador', 66),
	('EE', 'Estonia', 71), ('EG', 'Egypt', 67), ('EH', 'Western Sahara', 246), ('ER', 'Eritrea', 70),
	('ES', 'Spain', 203), ('ET', 'Ethiopia', 73), ('EU', 'European Union', 251), ('FI', 'Finland', 77),
	('FJ', 'Fiji', 76), ('FK', 'Falkland Islands', 74), ('FM', 'Micronesia', 143), ('FO', 'Faroe Islands', 75),
	('FR', 'France', 78), ('GA', 'Gabon', 82), ('GB', 'Great Britain', 237), ('GD', 'Grenada', 90),
	('GE', 'Georgia', 84), ('GF', 'French Guiana', 79), ('GG', 'Guernsey', 94), ('GH', 'Ghana', 86),
	('GI', 'Gibraltar', 87), ('GL', 'Greenland', 89), ('GM', 'Gambia', 83), ('GN', 'Guinea', 95),
	('GP', 'Guadeloupe', 91), ('GQ', 'Equatorial Guinea', 69), ('GR', 'Greece', 88), ('GS', 'South Georgia & South Sandwich Islands', 200),
	('GT', 'Guatemala', 93), ('GU', 'Guam', 92), ('GW', 'Guinea-Bissau', 96), ('GY', 'Guyana', 97),
	('HK', 'Hong Kong SAR China', 101), ('HM', 'Heard & McDonald Islands', 99), ('HN', 'Honduras', 100), ('HR', 'Croatia', 57),
	('HT', 'Haiti', 98), ('HU', 'Hungary', 102), ('ID', 'Indonesia', 105), ('IE', 'Ireland', 108),
	('IL', 'Israel', 110), ('IM', 'Isle of Man', 109), ('IN', 'India', 104), ('IO', 'British Indian Ocean Territory', 32),
	('IQ', 'Iraq', 107), ('IR', 'Iran', 106), ('IS', 'Iceland', 103), ('IT', 'Italy', 111),
	('JE', 'Jersey', 114), ('JM', 'Jamaica', 112), ('JO', 'Jordan', 115), ('JP', 'Japan', 113),
	('KE', 'Kenya', 117), ('KG', 'Kyrgyzstan', 120), ('KH', 'Cambodia', 38), ('KI', 'Kiribati', 118),
	('KM', 'Comoros', 51), ('KN', 'St. Kitts & Nevis', 207), ('KP', 'North Korea', 163), ('KR', 'South Korea', 201),
	('KW', 'Kuwait', 119), ('KY', 'Cayman Islands', 43), ('KZ', 'Kazakhstan', 116), ('LA', 'Laos', 121),
	('LB', 'Lebanon', 123), ('LC', 'St. Lucia', 208), ('LI', 'Liechtenstein', 127), ('LK', 'Sri Lanka', 204),
	('LR', 'Liberia', 125), ('LS', 'Lesotho', 124), ('LT', 'Lithuania', 128), ('LU', 'Luxembourg', 129),
	('LV', 'Latvia', 122), ('LY', 'Libya', 126), ('MA', 'Morocco', 149), ('MC', 'Monaco', 145),
	('MD', 'Moldova', 144), ('ME', 'Montenegro', 147), ('MF', 'St. Martin', 209), ('MG', 'Madagascar', 131),
	('MH', 'Marshall Islands', 137), ('MK', 'North Macedonia', 164), ('ML', 'Mali', 135), ('MM', 'Myanmar (Burma)', 151),
	('MN', 'Mongolia', 146), ('MO', 'Macao SAR China', 130), ('MP', 'Northern Mariana Islands', 165), ('MQ', 'Martinique', 138),
	('MR', 'Mauritania', 139), ('MS', 'Montserrat', 148), ('MT', 'Malta', 136), ('MU', 'Mauritius', 140),
	('MV', 'Maldives', 134), ('MW', 'Malawi', 132), ('MX', 'Mexico', 142), ('MY', 'Malaysia', 133),
	('MZ', 'Mozambique', 150), ('N/A', 'Not Available', 0), ('NA', 'Namibia', 152), ('NC', 'New Caledonia', 156),
	('NE', 'Niger', 159), ('NF', 'Norfolk Island', 162), ('NG', 'Nigeria', 160), ('NI', 'Nicaragua', 158),
	('NL', 'Netherlands', 155), ('NO', 'Norway', 166), ('NP', 'Nepal', 154), ('NR', 'Nauru', 153),
	('NU', 'Niue', 161), ('NZ', 'New Zealand', 157), ('OM', 'Oman', 167), ('PA', 'Panama', 171),
	('PE', 'Peru', 174), ('PF', 'French Polynesia', 80), ('PG', 'Papua New Guinea', 172), ('PH', 'Philippines', 175),
	('PK', 'Pakistan', 168), ('PL', 'Poland', 177), ('PM', 'St. Pierre & Miquelon', 210), ('PN', 'Pitcairn Islands', 176),
	('PR', 'Puerto Rico', 179), ('PS', 'Palestinian Territories', 170), ('PT', 'Portugal', 178), ('PW', 'Palau', 169),
	('PY', 'Paraguay', 173), ('QA', 'Qatar', 180), ('RE', 'Réunion', 181), ('RO', 'Romania', 182),
	('RS', 'Serbia', 190), ('RU', 'Russia', 183), ('RW', 'Rwanda', 184), ('SA', 'Saudi Arabia', 188),
	('SB', 'Solomon Islands', 197), ('SC', 'Seychelles', 191), ('SD', 'Sudan', 212), ('SE', 'Sweden', 215),
	('SG', 'Singapore', 193), ('SH', 'St. Helena', 206), ('SI', 'Slovenia', 196), ('SJ', 'Svalbard & Jan Mayen', 214),
	('SK', 'Slovakia', 195), ('SL', 'Sierra Leone', 192), ('SM', 'San Marino', 186), ('SN', 'Senegal', 189),
	('SO', 'Somalia', 198), ('SR', 'Suriname', 213), ('SS', 'South Sudan', 202), ('ST', 'São Tomé & Príncipe', 187),
	('SV', 'El Salvador', 68), ('SX', 'Sint Maarten', 194), ('SY', 'Syria', 217), ('SZ', 'Eswatini', 72),
	('TC', 'Turks & Caicos Islands', 230), ('TD', 'Chad', 45), ('TF', 'French Southern Territories', 81), ('TG', 'Togo', 223),
	('TH', 'Thailand', 221), ('TJ', 'Tajikistan', 219), ('TK', 'Tokelau', 224), ('TL', 'Timor-Leste', 222),
	('TM', 'Turkmenistan', 229), ('TN', 'Tunisia', 227), ('TO', 'Tonga', 225), ('TR', 'Turkey', 228),
	('TT', 'Trinidad & Tobago', 226), ('TV', 'Tuvalu', 231), ('TW', 'Taiwan', 218), ('TZ', 'Tanzania', 220),
	('UA', 'Ukraine', 235), ('UG', 'Uganda', 234), ('UK', 'United Kingdom', 255), ('UM', 'U.S. Outlying Islands', 232),
	('US', 'United States', 238), ('UY', 'Uruguay', 239), ('UZ', 'Uzbekistan', 240), ('VA', 'Vatican City', 242),
	('VC', 'St. Vincent & Grenadines', 211), ('VE', 'Venezuela', 243), ('VG', 'British Virgin Islands', 33), ('VI', 'U.S. Virgin Islands', 233),
	('VN', 'Vietnam', 244), ('VU', 'Vanuatu', 241), ('WF', 'Wallis & Futuna', 245), ('WS', 'Samoa', 185),
	('YE', 'Yemen', 247), ('YT', 'Mayotte', 141), ('YU', 'Yugoslavia', 254), ('ZA', 'South Africa', 199),
	('ZM', 'Zambia', 248), ('ZW', 'Zimbabwe', 249);
