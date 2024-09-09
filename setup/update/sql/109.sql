UPDATE public.config SET value = '109' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2024.09.08.2' WHERE setting = 'APP_VERSION';
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('109', '2024.09.08.2', 'Added document count 24 and processed document count to dashboard.', 'landtax', CURRENT_TIMESTAMP);

CREATE INDEX network_file_record_created_idx ON public.network_file (record_created);
