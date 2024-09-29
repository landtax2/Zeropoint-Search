UPDATE public.config SET value = '113' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2024.09.29.1' WHERE setting = 'APP_VERSION';
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('113', '2024.09.29.1', 'Created network_file_fulltext table.  This table is used to store the full text of a network file.', 'landtax', CURRENT_TIMESTAMP);


CREATE TABLE public.network_file_fulltext (
	id serial4 NOT NULL,
	network_file_id serial4 NOT NULL,
	full_text text NULL,
	CONSTRAINT network_file_fulltext_pkey PRIMARY KEY (id)
);
CREATE INDEX network_file_fulltext_full_text_idx ON public.network_file_fulltext USING gin (to_tsvector('english'::regconfig, full_text));
CREATE INDEX network_file_fulltext_network_file_id_idx ON public.network_file_fulltext USING btree (network_file_id);
ALTER TABLE public.network_file_fulltext ADD CONSTRAINT network_file_fulltext_network_file_fk FOREIGN KEY (id) REFERENCES public.network_file(id) ON DELETE CASCADE;

INSERT INTO public.config (setting, value, description, editable) VALUES ('STORE_FULLTEXT', '0', 'Used to determine if the full text of a network file should be stored in the database. 1 = store, 0 = do not store.', 1);
