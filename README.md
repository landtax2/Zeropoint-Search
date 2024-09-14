# Zeropoint-Search

This application is a comprehensive document search and analysis system, designed to handle file classification with advanced AI-powered features. It integrates various components for file processing, including text extraction, PII (Personally Identifiable Information) detection, and AI-based summarization and tagging. The system provides a web-based interface for users to search, view, and interact with the processed documents, as well as manage settings and generate reports.


# Installation

Use the docker compose file to install the application.  Once installed the application will provide you with additional instructions on how to setup the Ollama API interface.

Default username:  [not needed until user management is implemented]
Default password:  notsecue

The admin Password can be changed in the docker compose file by passing the `LOGIN_PASSWORD` environment variable or by changing it under the Settings/Configuration page once the application is running.

Currently this is no user account management.  This will be added in the future.



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
      - POSTGRES_PASSWORD=zps_database_password
      - POSTGRES_USER=zps_user
      - POSTGRES_DB=zps
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


