# Gretchen Image Gallery System

## Overview

Gretchen is a web-based image gallery and upload system built with PHP, JavaScript, CSS, and PostgreSQL.

It is designed to:

- upload images with optional title, description, and category
- display uploaded images in a gallery layout
- generate thumbnails for faster browsing
- filter images by category
- search images by title, description, or category
- track image view counts
- delete uploaded images

The project appears to be branded as an art or tourism gallery for the **Kalamansig Municipal Tourism ART HUB**.

## What The System Does

This system gives users a simple browser-based dashboard where they can manage a small media gallery.

Main user flows:

1. A user uploads an image through the form on the homepage.
2. The image file is saved into persistent storage.
3. The image metadata is saved into the PostgreSQL database.
4. A thumbnail is generated for display in the gallery.
5. Visitors can browse, search, filter, view, and delete images.

## Core Features

- Image upload with metadata
- Gallery-style image browsing
- Thumbnail generation
- Search by keyword
- Category filtering
- View counter per image
- Total image, category, and view statistics
- Image deletion from both database and storage
- Render-ready deployment with PostgreSQL

## Tech Stack

- Backend: PHP 8.2
- Database: PostgreSQL
- Database access: PDO
- Frontend: HTML, CSS, JavaScript
- Image processing: PHP GD
- Web server: Apache
- Deployment target: Render via Docker

## Project Structure

- [index.php](C:/Users/L_L_O/Desktop/deployment%20host/gretchen/index.php:1)
  Main page. Displays the upload form, stats, filters, and gallery.

- [config.php](C:/Users/L_L_O/Desktop/deployment%20host/gretchen/config.php:1)
  Shared configuration. Handles environment variables, PostgreSQL connection, storage paths, and table bootstrapping.

- [upload.php](C:/Users/L_L_O/Desktop/deployment%20host/gretchen/upload.php:1)
  Handles image uploads and inserts image metadata into the database.

- [delete.php](C:/Users/L_L_O/Desktop/deployment%20host/gretchen/delete.php:1)
  Deletes image records and removes stored image and thumbnail files.

- [view.php](C:/Users/L_L_O/Desktop/deployment%20host/gretchen/view.php:1)
  Updates and returns image view counts.

- [script.js](C:/Users/L_L_O/Desktop/deployment%20host/gretchen/script.js:1)
  Frontend interactivity for uploads, filtering, deletion, notifications, and view tracking.

- [style.css](C:/Users/L_L_O/Desktop/deployment%20host/gretchen/style.css:1)
  Main styling for the whole interface.

- [image_gallery.sql](C:/Users/L_L_O/Desktop/deployment%20host/gretchen/image_gallery.sql:1)
  PostgreSQL schema and sample seed data.

- [Dockerfile](C:/Users/L_L_O/Desktop/deployment%20host/gretchen/Dockerfile:1)
  Docker image definition for PHP, Apache, GD, and PostgreSQL support.

- [render.yaml](C:/Users/L_L_O/Desktop/deployment%20host/gretchen/render.yaml:1)
  Render service configuration.

- [RENDER_DEPLOY.md](C:/Users/L_L_O/Desktop/deployment%20host/gretchen/RENDER_DEPLOY.md:1)
  Render deployment instructions.

- `images/`
  Stores uploaded original images in local development if `STORAGE_ROOT` points to the project.

- `thumbs/`
  Stores generated thumbnails in local development if `STORAGE_ROOT` points to the project.

- `storage/`
  Recommended persistent storage root in deployment environments such as Render.

## How It Works

### Upload flow

- The homepage form submits an image and optional metadata to `upload.php`.
- The file is validated by MIME type.
- A unique filename is generated.
- The image is saved to the configured storage path.
- The database stores:
  - filename
  - title
  - description
  - category
  - upload timestamp
  - view count

### Gallery flow

- `index.php` reads records from the `images` table.
- It supports optional keyword search and category filtering.
- If a thumbnail does not exist yet, the system creates one on demand.
- The gallery displays either the thumbnail or the original image.

### View tracking

- Opening or clicking an image triggers a request to `view.php`.
- The image `views` counter is incremented in PostgreSQL.
- The updated view count and total view count are returned to the page.

### Delete flow

- Clicking delete opens a confirmation modal.
- Confirming deletion sends a request to `delete.php`.
- The image row is removed from the database.
- The stored original file and thumbnail are removed from storage.

## Database

The application uses one main table:

- `images`

Columns:

- `id`
- `filename`
- `title`
- `description`
- `category`
- `uploaded_at`
- `views`

The schema is auto-created in `config.php` if it does not exist.

## Environment Variables

The system supports these environment variables:

- `DATABASE_URL`
  Primary PostgreSQL connection string. Recommended for Render.

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `DB_SSLMODE`
  Optional fallback variables for local PostgreSQL setup if `DATABASE_URL` is not used.

- `STORAGE_ROOT`
  Root folder for uploaded images and thumbnails.

## Local Development

To run this project locally, you need:

- PHP 8.2 or compatible
- PostgreSQL
- GD extension enabled

Basic local setup:

1. Create a PostgreSQL database.
2. Set `DATABASE_URL` or the fallback DB environment variables.
3. Import `image_gallery.sql` if you want sample data.
4. Make sure `STORAGE_ROOT` points to a writable folder.
5. Serve the project with PHP or Apache.

Example database import:

```bash
psql "$DATABASE_URL" -f image_gallery.sql
```

## Deployment

This project is prepared for deployment on Render using Docker.

Deployment essentials:

- a Render Web Service
- a Render PostgreSQL database
- a persistent disk mounted at `/var/www/html/storage`

See [RENDER_DEPLOY.md](C:/Users/L_L_O/Desktop/deployment%20host/gretchen/RENDER_DEPLOY.md:1) for the deployment steps.

## Notes And Limitations

- Image validation currently checks MIME type from the uploaded file metadata, so stricter server-side validation could be added later.
- Thumbnail creation currently supports JPEG, PNG, and GIF in the thumbnail generator logic.
- Uploaded files depend on writable storage and should be backed by a persistent disk in production.
- This is a small monolithic PHP app, so all backend behavior is handled through individual PHP endpoints rather than a larger framework.

## Intended Use

This system is suitable for:

- school projects
- municipal gallery showcases
- small tourism or art image hubs
- simple internal media libraries

It is best suited for small to medium galleries where a lightweight PHP-based upload and display system is enough.
