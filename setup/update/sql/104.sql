UPDATE public.config SET value = '104' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2024.09.06.3' WHERE setting = 'APP_VERSION';
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('104', '2024.09.06.3', 'Testing the changelog table and updates.', 'landtax', CURRENT_TIMESTAMP);