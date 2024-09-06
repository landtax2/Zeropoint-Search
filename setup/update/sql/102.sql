UPDATE public.config SET value = '102' WHERE setting = 'DB_VERSION';
INSERT INTO public.config (setting, value, description) VALUES ('APP_VERSION', '2024.09.06.1', 'The version of the application');