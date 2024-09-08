UPDATE public.config SET value = '107' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2024.09.07.4' WHERE setting = 'APP_VERSION';
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('107', '2024.09.07.4', 'Added tags table.', 'landtax', CURRENT_TIMESTAMP);

CREATE TABLE public.tag (
	id bigserial NOT NULL,
	network_file_id int4 NOT NULL,
	tag varchar(250) NOT NULL,
	CONSTRAINT tag_pk PRIMARY KEY (id)
);
CREATE INDEX tag_network_file_id_idx ON public.tag USING btree (network_file_id);
CREATE INDEX tag_tag_idx ON public.tag USING btree (tag);

ALTER TABLE public.tag ADD CONSTRAINT tag_network_file_fk FOREIGN KEY (network_file_id) REFERENCES public.network_file(id) ON DELETE CASCADE;

ALTER TABLE public.network_file ADD CONSTRAINT network_file_client_fk FOREIGN KEY (client_id) REFERENCES public.client(id) ON DELETE CASCADE;
