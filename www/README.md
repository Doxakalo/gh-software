# www.24usoftware.com

Použité technologie:
- Nette 4.0
- PHP 8.4
- FM Databáze / fmRESTor 19.0 
- Composer
- MySQL

## Instalace:

- Je potřeba zduplikovat konfigurační soubor "config.local.default.neon" a přejmenovat ho na "config.local.neon". 
- Je potřeba zduplikovat konfigurační soubor "config.api.local.default.neon" a přejmenovat ho na "config.api.local.neon".
- Je potřeba nainstalovat všechny závislosti ( package.json a composer.json )
- Spustit kompilační nástroje


- Přesunout se do folderu "migrations" a migrovat data  
    - přemigruje základní strukturu

    ```bash
    cd migrations
    php migrations-db.php structure migrations:migrate
    php migrations-db.php sample migrations:migrate
    ```

### Docker
- Pro spuštění v dockeru je potřeba vyplnit proměnné v .env

    ```
    docker compose up -d
    ```
- Dostat se dovnitř kontaineru a provést migrace
    ```
    docker exec -it #container-php-apache-1 bash
    cd migrations
    php migrations-db.php structure migrations:migrate
    php migrations-db.php sample migrations:migrate
    ```
