# Zeropoint-Search


Default password:  notsecue

Password can be changed in the docker compose file by passing the `ZPS_ADMIN_PASSWORD` environment variable.

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


