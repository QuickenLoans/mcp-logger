# Integration tests

These tests are designed to run within the `docker-compose` configuration provided with
this project.

These tests can also be run from your shell, but PHP changes some configuration settings
when run from SAPI and these tests allow you to ensure messages are logged to the correct
location from CLI or SAPI.

## Usage

```
cd $MCP_LOGGER_PROJECT_ROOT

# 1. Start containers
docker-compose up -d

# 2. Open `http://localhost:8080/` in web browser
open "http://localhost:8080"

# 3. Select a test to run it.

# 4. Verify messages
docker-compose exec web tail -n 10 /var/log/messages
```
