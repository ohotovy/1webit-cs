### Instructions

Follow the instructions to get the project running (starts in project root)

1. copy `.env.example` as `.env` and fill in your values
1. copy `/config/local.neon.example` as `/config/local.neon` and fill in your values
1. `cd docker`
1. `docker compose --env-file ../.env up`
1. `docker exec -it 1webit-shop-app bash`
1. (in container bash) `composer install`
1. (in container bash) `# php bin/doctrine orm:schema-tool:create`
1. (in container bash) `php seed_tables.php`
1. Go to `localhost:8000` in browser or `localhost:8000/admin` for admin section.

In case it doesn't work step by step, apologies, still not THAT good w/ Docker and "It works on my machine(tm)". Please don't hesitate to ask on `ondrej.hotovy@outlook.com`.