UPDATE public.config SET value = '115' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2025.04.11.1' WHERE setting = 'APP_VERSION';
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('115', '2025.04.11.1', 'Chunk settings', 'landtax', CURRENT_TIMESTAMP);

INSERT INTO public.config (setting, value, description, editable) VALUES ('CHUNK_WORDS', '300', 'Used to determine the number of words per chunk. 300 is the default.', 1);
INSERT INTO public.config (setting, value, description, editable) VALUES ('CHUNK_OVERLAP', '70', 'Used to determine the number of words per chunk. 70 is the default.', 1);
