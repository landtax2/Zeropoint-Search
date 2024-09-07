UPDATE public.config SET value = '106' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2024.09.07.2' WHERE setting = 'APP_VERSION';
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('106', '2024.09.07.3', 'Added fields for credentials and bank.', 'landtax', CURRENT_TIMESTAMP);

ALTER TABLE public.network_file
ADD COLUMN ai_credentials VARCHAR(10),
ADD COLUMN ai_bank VARCHAR(10);
