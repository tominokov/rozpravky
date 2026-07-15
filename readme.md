# Audio Story Web Application

Ľahká, rýchla a bezpečná objektovo orientovaná PHP webová aplikácia slúžiaca na prehrávanie audiopríbehov s integráciou Google OAuth2 autentifikácie, správou používateľských nastavení a ukladaním obľúbených položiek.

## 🚀 Hlavné vlastnosti

- **Google OAuth2 Autentifikácia:** Bezpečné prihlasovanie používateľov bez nutnosti správy hesiel.
- **Podpora databáz:** Pripravená architektúra pre MySQL aj SQLite.
- **Používateľské nastavenia:** Personalizované ukladanie konfigurácie (hlasitosť, náhodné prehrávanie).
- **Systém obľúbených:** Možnosť pridávať a odoberať príbehy z vlastného zoznamu.

## 🛠️ Architektúra a technológie

Projekt je napísaný v jazyku PHP:
- PHP 8.2+
- Composer (PSR-4 autoloading, integrácia Google API Client a vlucas/phpdotenv)
- PDO

---

## 📋 Požiadavky

- PHP >= 8.2
- MySQL >= 5.7 alebo SQLite 3
- Composer

---

## 🔧 Inštalácia a nastavenie

1. Klonovanie repozitára:

   ```bash
   git clone https://github.com/tominokov/rozpravky.git \
   cd rozpravky
   ```

2. Inštalácia závislostí:

   ```bash
   composer install
   ```

4. Konfigurácia prostredia:

   Skopírujte vzorový súbor prostredia a upravte v ňom svoje prístupové údaje:
   
   ```bash
   cp .env.example .env
   ```

   Otvorte .env a nakonfigurujte databázu a Google OAuth kľúče:

   ```
   APP_URL="http://localhost:8000"
   DB_HOST="127.0.0.1"
   DB_NAME="tvoja_databaza"
   DB_USER="tvoj_pouzivatel"
   DB_PASS="tvoje_heslo"

   GOOGLE_AUTH_CLIENT_ID="tvoj-client-id.apps.googleusercontent.com"
   GOOGLE_AUTH_CLIENT_SECRET="tvoj-client-secret"
   GOOGLE_AUTH_REDIRECT_URI="http://localhost:8000/auth/callback"
   ```
   
6. Spustenie lokálneho servera:

   ```bash
   php -S localhost:8000 -t public
   ```
