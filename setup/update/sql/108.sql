UPDATE public.config SET value = '108' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2024.09.08.1' WHERE setting = 'APP_VERSION';
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('108', '2024.09.08.1', 'Added indexes to network_file table for dashboard.', 'landtax', CURRENT_TIMESTAMP);

CREATE INDEX network_file_ssn_hard_idx ON public.network_file (ssn_hard);
CREATE INDEX network_file_ai_severity_idx ON public.network_file (ai_severity);
CREATE INDEX network_file_ai_credentials_idx ON public.network_file (ai_credentials);
