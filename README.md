# HostelCare — Hostel Complaint System

A responsive PHP + MySQL hostel complaint management app with student accounts, complaint tracking, admin triage, status updates, search/filtering, priority labels, and a Docker Compose / Docker Swarm deployment.

## Requirements
- Docker Desktop (Mac / Windows) or Docker Engine + Compose plugin
- This project folder on disk

## 1. Configure
Copy `.env.example` to `.env` and edit both passwords:

```bash
cp .env.example .env
open -e .env
```

Use strong local passwords. Do not commit `.env` to GitHub.

## 2. Start using Compose
Run in this folder:

```bash
docker compose up -d --build
docker compose ps
```

Open http://localhost:8080

## 3. Create an administrator
Create a secure admin account using the CLI. Replace the sample email/name/password:

```bash
docker compose exec app php scripts/create_admin.php admin@example.com "Hostel Administrator" "ChangeThisToAStrongPassword123"
```

Sign in using the email and password you provided. Register ordinary student accounts using **Create an account** on the login page.

## 4. Test features
1. Register a student account.
2. Sign in and create a complaint.
3. Sign out.
4. Sign in using the admin account.
5. Open **Admin console**, review the complaint, change its status, and add an admin note.
6. Sign back in as the student and view the updated complaint.

## 5. Useful commands
```bash
docker compose logs --tail=100 app
docker compose logs --tail=100 db
docker compose restart app
docker compose down
```

To delete all local database data too (destructive):
```bash
docker compose down -v
```

## 6. Docker Swarm
Build the web image on the current machine:

```bash
docker build -t hostelcare-app:1.0 .
docker swarm init
set -a
source .env
set +a
docker stack deploy -c stack.swarm.yaml hostelcare
docker stack services hostelcare
docker service ps hostelcare_app
```

Open http://localhost:8080. Scale the stateless PHP app:

```bash
docker service scale hostelcare_app=3
docker service ps hostelcare_app
```

Remove the stack:
```bash
docker stack rm hostelcare
```

**Swarm notes:** this local setup demonstrates stack deployment and web replicas on one node. It is not multi-node high availability. The local image is suitable for a single-node Docker Desktop demo; for multiple nodes, publish the app image to a registry available to all nodes. MySQL must remain one replica with persistent storage. Do not run Compose and Swarm on the same port at the same time.

## Database initialization
`database.sql` is imported only when MySQL initializes an empty data directory. If you change the schema after the volume exists, use a migration or intentionally reset the volume (which deletes data).

## Security notes
- Prepared statements are used for database writes and login lookups.
- Passwords are stored using PHP `password_hash` and verified with `password_verify`.
- Forms use CSRF tokens and output is escaped.
- New public registrations always receive the student role; create admins only through the container CLI.
- This is an educational project. For production, add HTTPS, rate limiting, audit logging, backups, and a dedicated least-privilege database account.
