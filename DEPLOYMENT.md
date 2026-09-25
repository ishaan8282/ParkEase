# Free deployment (Laravel + Vue + MySQL)

This repository is configured for a **free hobby/demo deployment** using:

- **Render** for the Laravel/PHP web service. The Vue + Inertia frontend is compiled with Vite and served by Laravel from the same service.
- **TiDB Cloud Starter** for a managed, MySQL-compatible SQL database. The app uses its existing MySQL migrations, including a migration with MySQL-specific `ALTER TABLE ... ENUM` syntax.

The Render service uses the Dockerfile in this repository. `render.yaml` is a Blueprint that sets up the service defaults, builds the Vue assets, generates `APP_KEY`, runs migrations on startup, and starts Laravel's scheduler alongside Apache.

> This is suitable for a class project, demo, or hobby app—not a production service. Render's free web services spin down after 15 minutes without inbound traffic, then take about a minute to wake. Free services also have monthly instance-hour limits and ephemeral local files. See [Render's free-instance limitations](https://render.com/docs/free). TiDB's free quota and limits can change; check its [current pricing](https://www.pingcap.com/tidb-cloud/) before relying on it.

## 1. Push this repository to GitHub

Render deploys from a Git repository. Push the branch/repository you want to deploy to GitHub first. Do not add `.env` or database credentials to Git.

## 2. Create the free SQL database

1. Sign up at [TiDB Cloud](https://tidbcloud.com/) and create a **Starter** cluster (the free/serverless option).
2. Create or select a database; TiDB's default `test` database is fine.
3. Open the cluster's **Connect** dialog and copy the MySQL connection details. Use the values from TiDB, not the example placeholders below:
   - Hostname → `DB_HOST`
   - Port (normally `4000`) → `DB_PORT`
   - Database name → `DB_DATABASE`
   - Username → `DB_USERNAME`
   - Generated password → `DB_PASSWORD`
4. TiDB Starter requires TLS. The Render Blueprint configures PHP's MySQL driver to use the container's CA bundle. Keep TLS enabled in the TiDB connection settings. TiDB's [quick start](https://docs.pingcap.com/tidbcloud/tidb-cloud-quickstart/) explains where to find connection parameters.
5. If the cluster requires an IP allowlist, Render's free service does not provide a stable outbound IP. For a demo, allow access from anywhere in TiDB's network settings and rely on the generated database password and TLS; restrict access again if you move to a host with stable egress IPs.

## 3. Deploy the Render Blueprint

1. Create a free account at [Render](https://render.com/).
2. From the dashboard choose **New → Blueprint** and connect the GitHub repository containing ParkEase.
3. Render will detect `render.yaml`. Review the service and choose the **Free** plan.
4. In the service's **Environment** settings, set the TiDB values for `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. `render.yaml` marks the host, username, and password as private values; do not put secrets in the file or in source control.
5. Apply the Blueprint and wait for the first deploy. Render builds the PHP dependencies and Vue assets from the Dockerfile. At startup, it runs `php artisan migrate --force` and then starts Apache and `php artisan schedule:work`.
6. Open the service URL ending in `onrender.com`. Laravel reads `RENDER_EXTERNAL_URL` to generate its public URL.

If you already created the service without the Blueprint, create a Docker Web Service from this repository and use the same environment values as `render.yaml`. The Dockerfile listens on port `10000` and serves Laravel's `public/` directory.

## Free-tier limitations to plan for

- **Cold starts:** an idle Render free service sleeps; the next visitor may wait roughly a minute. While it is asleep, the Laravel scheduler is not running, so expired reservations/bookings are cleaned up when the service is awake again.
- **Uploads and generated payslips:** Render's container filesystem is ephemeral. Uploaded images and generated PDFs can disappear on restart, spin-down, or redeploy. Use an external object-storage service and configure Laravel's filesystem before storing anything important.
- **Database access:** do not use a blank or reused password. Keep TiDB credentials in Render's environment settings, never in source code. Back up any data you care about.
- **Usage/limits:** Render provides 750 free instance hours per workspace per month, shared by its free web services. TiDB has a separate free usage quota. Exceeding a provider's limit can suspend the free service or incur charges depending on the provider/account setup.
- **No deployment was triggered by this repository change:** you need to connect your own Render and TiDB accounts and provide the database connection details.

## Local build check

The production image builds with Docker:

```bash
docker build -t parkease .
docker run --rm -p 10000:10000 \
  -e APP_KEY='base64:replace-with-a-generated-key' \
  -e DB_CONNECTION=mysql \
  -e DB_HOST=your-tidb-host \
  -e DB_PORT=4000 \
  -e DB_DATABASE=test \
  -e DB_USERNAME=your-tidb-user \
  -e DB_PASSWORD=your-tidb-password \
  parkease
```

For production, generate a unique Laravel key and use the value generated for the Render service; never use the placeholder above.
