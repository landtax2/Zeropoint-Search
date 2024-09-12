UPDATE public.config SET value = '112' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2024.09.11.2' WHERE setting = 'APP_VERSION';
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('112', '2024.09.11.2', 'Added Stirling PDF API to the application.', 'landtax', CURRENT_TIMESTAMP);

INSERT INTO public.config (setting, value) VALUES ('STIRLING_PDF_API', 'http://stirling:8080/api/v1/convert/file/pdf');
