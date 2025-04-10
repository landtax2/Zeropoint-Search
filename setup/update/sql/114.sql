UPDATE public.config SET value = '114' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2025.04.09.1' WHERE setting = 'APP_VERSION';
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('114', '2025.04.09.1', 'Chunk text storage', 'landtax', CURRENT_TIMESTAMP);

CREATE TABLE public.network_file_chunk (
	id serial4 NOT NULL,
	network_file_id serial4 NOT NULL,
	chunk_seq int4 NOT NULL,
	chunk_text_no_overlap text NULL,
	chunk_text_overlap text NULL,
	CONSTRAINT network_file_chunk_pkey PRIMARY KEY (id)
);
CREATE INDEX network_file_chunk_chunk_network_file_id_idx ON public.network_file_chunk USING btree (network_file_id);
CREATE INDEX network_file_chunk_chunk_text_no_overlap_idx ON public.network_file_chunk USING gin (to_tsvector('english'::regconfig, chunk_text_no_overlap));
CREATE INDEX network_file_chunk_chunk_text_overlap_idx ON public.network_file_chunk USING gin (to_tsvector('english'::regconfig, chunk_text_overlap));

ALTER TABLE public.network_file_chunk ADD CONSTRAINT network_file_chunk_network_file_fk FOREIGN KEY (network_file_id) REFERENCES public.network_file(id) ON DELETE CASCADE ON UPDATE CASCADE;