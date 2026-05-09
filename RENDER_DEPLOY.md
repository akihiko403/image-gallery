# Deploying This Project on Render

This project is a PHP + Apache + PostgreSQL image gallery.

## What this app needs

- A Render `Web Service` running from Docker
- A Render PostgreSQL database
- A persistent disk for uploaded images and thumbnails

## Render-friendly setup

This app now uses PostgreSQL through `PDO`, which fits Render's managed Postgres offering much better than the earlier MySQL version.

## Files added for deployment

- `Dockerfile`: runs the app with Apache, `pdo_pgsql`, and `gd`
- `render.yaml`: starter Render blueprint for the web service
- `config.php`: reads `DATABASE_URL` and bootstraps the PostgreSQL table
- `image_gallery.sql`: PostgreSQL schema + sample seed data

## Render web service setup

Create a new Render `Web Service` from this repo.

Use these settings:

- Runtime: `Docker`
- Dockerfile Path: `./Dockerfile`
- Branch: your deploy branch

Set these environment variables:

- `DATABASE_URL`
- `STORAGE_ROOT=/var/www/html/storage`

## Persistent disk

Attach a persistent disk to the web service.

Use:

- Mount path: `/var/www/html/storage`

This is required because Render's filesystem is ephemeral by default, so uploaded files would otherwise disappear after redeploys or restarts.

## Database import

After your Render Postgres database is created, import `image_gallery.sql`.

If you do not import it, the app will still auto-create the `images` table, but your sample records will not be there.

Example import command:

```bash
psql "$DATABASE_URL" -f image_gallery.sql
```

## Recommended deploy flow

1. Push this project to GitHub
2. Create a Render PostgreSQL database in the same region
3. Create the Render web service from the repo
4. Copy the database `DATABASE_URL` into the web service environment variables
5. Attach the persistent disk at `/var/www/html/storage`
6. Import `image_gallery.sql` into Postgres
7. Deploy

## What to expect after deploy

- The site should load at Render's `onrender.com` URL
- New uploads will be stored under the persistent disk
- Thumbnails will also be stored on the persistent disk
