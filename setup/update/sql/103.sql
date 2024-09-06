UPDATE public.config SET value = '103' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2024.09.06.2' WHERE setting = 'APP_VERSION';

CREATE TABLE public.changelog (
	id serial4 NOT NULL,
	database_version varchar(10) NOT NULL,
	application_version varchar(20) NOT NULL,
	change_summary varchar(1000) NOT NULL,
	author varchar(100) NOT NULL,
	date_created timestamp NOT NULL,
	CONSTRAINT changelog_pkey PRIMARY KEY (id)
);
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('103', '2024.09.06.2', 'Added the changelog table to track changes to the database and application.', 'landtax', CURRENT_TIMESTAMP);

