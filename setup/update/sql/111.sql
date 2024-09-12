UPDATE public.config SET value = '111' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2024.09.11.1' WHERE setting = 'APP_VERSION';
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('111', '2024.09.11.1', 'Removed time zone configuration from the database.', 'landtax', CURRENT_TIMESTAMP);

DELETE FROM public.config WHERE setting = 'TIME_ZONE';
