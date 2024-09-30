# Status

This is currently in the early stages of development and should not be used in a production environment.  Future updates may break prior installations.  This application should only be deployed in a LAN environment and not exposed to the public internet.

# Zeropoint-Search

This application is a comprehensive document search and analysis system, designed to handle file classification with advanced AI-powered features. It integrates various components for file processing, including text extraction, PII (Personally Identifiable Information) detection, and AI-based summarization and tagging. The system provides a web-based interface for users to search, view, and interact with the processed documents, as well as manage settings and generate reports.

With luck, this should be able to scale to handle a large number of documents. 

# Installation

Use the docker compose file to install the application.  Once installed the application will provide you with additional instructions on how to setup the Ollama API interface.

Default username:  [not needed until user management is implemented]  
Default password:  notsecure  

The admin Password can be changed in the docker compose file by passing the `LOGIN_PASSWORD` environment variable or by changing it under the Settings/Configuration page once the application is running.

Currently this is no user account management.  This will be added in the future.

Warning: After first launch, the envirment variables for the postgres database will not be changed if they are changed in the compose. Changing the variables such as the password will require manually changing it in the postgres container.

### Docker Compose

```yaml
version: '3'

services:
  zeropoint:
    image: landtax76/zeropoint_search:latest
    container_name: zps_front_end
    ports:
      - 8092:80
    volumes:
      - ./env:/var/www/html/.env
    environment:
      - DB_PASS=zps_database_password
      - DB_USER=zps_user
      - DB_HOST=zps_db
      - DB_NAME=zps
      - DB_PORT=5432
      - DEBUGGING=1
      - TZ=America/New_York  
    restart: unless-stopped
  postgres:
    image: postgres:latest
    container_name: zps_db
    volumes:
      - ./postgres_data:/var/lib/postgresql/data
    environment:
      - POSTGRES_PASSWORD=zps_database_password
      - POSTGRES_USER=zps_user
      - POSTGRES_DB=zps
      - TZ=America/New_York
    restart: unless-stopped
  stirling:
    image: frooodle/s-pdf:latest
    container_name: zps_stirling
    environment:
      - DOCKER_ENABLE_SECURITY=false
      - INSTALL_BOOK_AND_ADVANCED_HTML_OPS=false
      - LANGS=en_GB
    restart: unless-stopped
  doctor:
    image: freelawproject/doctor:latest
    container_name: zps_doctor
    restart: unless-stopped
``` 

### Dockerfile

```Dockerfile
FROM php:8.2.23-apache-bookworm

# Update and install required packages
RUN apt update && apt install -y --no-install-recommends libpq-dev git

# Install necessary PHP extensions
RUN docker-php-ext-install pdo_pgsql

#clone the repo
RUN git clone https://github.com/landtax2/Zeropoint-Search.git /var/www/html

#Turns on the htaccess file
RUN mv /var/www/html/htaccess /var/www/html/.htaccess

# Change ownership of the html directory to the Apache user
RUN chown -R www-data:www-data /var/www/html

```
# Further documentation

Documentation can be found in the the application.

# Planned Features

- User Management
- Multi-tenancy
- More comprehensive tagging/categorization
- More comprehensive search
- More comprehensive analytics
- More comprehensive reporting
- More comprehensive security
- Remediation of PII
