UPDATE public.config SET value = '106' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2024.09.07.2' WHERE setting = 'APP_VERSION';
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('106', '2024.09.07.2', 'Added config settings for summary length and processing context window.', 'landtax', CURRENT_TIMESTAMP);


