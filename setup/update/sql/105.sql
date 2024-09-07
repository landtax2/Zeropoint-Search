UPDATE public.config SET value = '105' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2024.09.07.1' WHERE setting = 'APP_VERSION';
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('105', '2024.09.07.1', 'Adding indexes to the network_file table.', 'landtax', CURRENT_TIMESTAMP);

CREATE INDEX network_file_ai_pii_ssn_idx ON public.network_file (ai_pii_ssn);
CREATE INDEX network_file_client_id_idx ON public.network_file (client_id);
CREATE INDEX network_file_path_idx ON public.network_file ("path");
CREATE INDEX network_file_name_idx ON public.network_file ("name");
CREATE INDEX network_file_hash_idx ON public.network_file (hash);
