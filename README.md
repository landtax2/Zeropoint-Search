# Zeropoint-Search

Setup guide for the frontend:

1. Setup postgres database.  Create a database called "zps" and a user called "zps_user". 
You can use this docker compose to quickly do this, or do it the hard way by installing and configuring postgres manually:

version: '3.3'

services:
  postgres:
    image: postgres:latest
    ports:
      - 5432:5432
    volumes:
      - ./postgres_data:/var/lib/postgresql/data
    environment:
      - POSTGRES_PASSWORD=changeme
      - POSTGRES_USER=zps_user
      - POSTGRES_DB=zps


2. Setup a webserver to run PHP v8.1 or later.  Enable CURL and PDO Postgres extensions in the PHP config.
3. Clone this repository to your webserver.
4. Setup a .env file in the root containing the following:

DEBUGGING="1"
DB_HOST="ip or name of postgres server"
DB_NAME="zps"
DB_USER="zps_user"
DB_PASS="changme"
DB_PORT="5434"

5. Navigate to the root file in a browser.  The database and tables will be created automatically.

6. Login with the password of "notsecure".   There is no need for a username.

7. See instructions on the dashboard page on how to configure the rest of needed API endpoints.

