# Reservations System

[![Deploy to DO](https://www.deploytodo.com/do-btn-blue.svg)](https://cloud.digitalocean.com/apps/new?repo=https://github.com/shahednasser/reservations-system/tree/master)

## Requirements

1. PHP 7
2. Composer 1
3. MySQL
4. Created Empty Database in MySQL
5. [Pusher Account](https://pusher.com/)
6. [OneSignal Account](https://onesignal.com/)
7. For Pusher: NPM

## Installation Instructions

1. Clone repository
2. run `composer install`
3. rename `.env.example` to `.env` and change the following variables:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

4. Set Pusher Variables in `.env`:

```bash
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=
```

5. Set OneSignal variables in `.env`:

```bash
ONESIGNAL_APP_ID=
ONESIGNAL_REST_API_KEY=
```

## Build Assets

1. Install dependencies:

```bash
npm install
```

2. Build Dependencies:

```bash
npm run production
```

## Start Server

```bash
php artisan serve
```