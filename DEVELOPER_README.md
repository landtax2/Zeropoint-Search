Intended for developers to understand the codebase and how to modify it.

# Updating the version
This should be done whenever changes are made to the database schema or there are significant changes to the codebase.

Step 1:  Create the next sequential number .SQL of the database version in the setup/update/sql/ directory. Eg.  101.sql, 102.sql, 103.sql
Step 2:  Add the SQL to the file to update the DB_VERSION and APP_VERSION in the config table (as well as any other changes you want to make)
Step 3:  Update the database version in the top of the common.php file (see classes/common.php)

The APP_VERSION format is as follow:
2024: Year of the release.
09: Month of the release.
06: Day of the release.
1: Revision or build number for that day.
Eg. 2024.09.06.1


These 3 lines should be added to each update file:
!!!BE SURE TO KEEP THE VERSIONS CONSISTENT IN THE BELOW QUERIES!!!
UPDATE public.config SET value = '104' WHERE setting = 'DB_VERSION';
UPDATE public.config SET value = '2024.09.06.3' WHERE setting = 'APP_VERSION';
INSERT INTO public.changelog (database_version, application_version, change_summary, author, date_created) VALUES ('104', '2024.09.06.3', 'Testing the changelog table and updates.', 'landtax', CURRENT_TIMESTAMP);

Test the updates before commiting the changes to the main branch.
