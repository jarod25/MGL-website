# Mulhouse Gaming Tournament Website

Web platform developed for the Mulhouse Gaming Tournament to manage participant registration, online payments and team enrolment for a multi-game LAN event.

The application covers the complete registration workflow, from selecting an admission option to validating the HelloAsso payment and joining or creating a team.

## Main features

- participant registration and account creation
- one-day and two-day admission options
- HelloAsso payment integration
- authenticated webhook processing and payment reconciliation
- registration status tracking
- team creation and member management
- game-specific roster sizes and team limits
- prevention of conflicting registrations for the same game
- day restrictions for one-day participants
- administration interfaces for participants, subscriptions, games and teams
- multilingual public content in French, English and German
- tournament information and regulations

## Supported games

The current tournament configuration includes:

- League of Legends
- Valorant
- Rocket League
- EA Sports FC 26

Game availability, team size, team capacity and event day are stored as configurable application data.

## Payment workflow

Payments are handled through HelloAsso.

The application:

1. creates a pending participant registration;
2. redirects the participant to the configured HelloAsso form;
3. receives and authenticates HelloAsso webhook events;
4. matches payments with participants and admission tiers;
5. records successful, unresolved, conflicting or refunded payments;
6. unlocks team registration only for eligible paid participants.

The payment integration is designed to handle duplicate notifications, unexpected forms, identity or amount mismatches and manual reconciliation of unresolved payments.

## Technology stack

### Backend

- PHP 8.2
- Symfony 7.4
- Doctrine ORM and Doctrine Migrations
- MariaDB 10.6
- Twig

### Frontend

- Symfony UX and Stimulus
- Webpack Encore
- Bootstrap
- Sass

### Integration and delivery

- HelloAsso API and webhooks
- Docker
- Nginx
- GitHub Actions

## Project structure

The application follows the standard Symfony directory structure:

- `src/Controller/` contains HTTP and administration controllers
- `src/Entity/` contains the Doctrine domain model
- `src/Service/` contains business rules and external-service integrations
- `src/Repository/` contains database access logic
- `src/Form/` contains Symfony forms
- `templates/` contains Twig views
- `translations/` contains French, English and German translations
- `migrations/` contains database schema migrations
- `tests/` contains automated tests and integration fixtures

LAN-specific tables and domain components use the `lan__` prefix to remain separate from the original application modules.

## Local requirements

- PHP 8.2 with the required extensions
- Composer
- Node.js and npm
- MariaDB 10.6 or a compatible version
- a local web server such as Nginx

## Local setup

Install PHP dependencies:

```bash
composer install
```

Install and build frontend assets:

```bash
npm install
npm run build
```

Create a `.env.local` file and configure at least:

```dotenv
APP_SECRET=<local-secret>
DATABASE_URL="mysql://<user>:<password>@127.0.0.1:3306/<database>?serverVersion=mariadb-10.6.0&charset=utf8mb4"
MAILER_DSN=<mailer-dsn>
```

Create the database and apply the migrations:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Development fixtures can be loaded when working with a disposable local database:

```bash
php bin/console doctrine:fixtures:load
```

Configure the web server document root to the `public/` directory.

## HelloAsso configuration

The integration uses the following environment variables:

```dotenv
HELLOASSO_WIDGET_URL=<widget-url>
HELLOASSO_PUBLIC_URL=<public-form-url>
HELLOASSO_ORGANIZATION_SLUG=<organization-slug>
HELLOASSO_FORM_TYPE=<form-type>
HELLOASSO_FORM_SLUG=<form-slug>
HELLOASSO_WEBHOOK_SECRET=<webhook-secret>
HELLOASSO_WEBHOOK_ALLOWED_IPS=<optional-allowed-ips>
HELLOASSO_ALLOW_UNSIGNED_WEBHOOKS=0
```

Real credentials and production secrets must be stored outside committed environment files.

## Tests

Run the PHP test suite with:

```bash
php bin/phpunit
```

## Project status

The platform is actively maintained for the Mulhouse Gaming Tournament. Features and business rules evolve according to the event organisation requirements.

## License

This project is not distributed under an open-source license.
