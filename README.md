# Taskey Backend

Taskey Backend is a RESTful API built with Laravel, serving as the backend for the Taskey project management application. It provides endpoints for project, task, user, and activity management, with authentication and role-based access.

## Features
- User authentication (with GitHub OAuth)
- Project CRUD and member management
- Task CRUD and assignment
- User profile management
- Activity tracking

## Requirements
- PHP >= 8.2
- Composer
- Node.js & npm (for asset building)
- MySQL or SQLite (or other supported DB)

## Installation
1. **Clone the repository:**
   ```bash
   git clone <repo-url>
   cd Taskey_backend
   ```
2. **Install PHP dependencies:**
   ```bash
   composer install
   ```
3. **Install Node dependencies:**
   ```bash
   npm install
   ```
4. **Copy and configure environment:**
   - Copy `.env.example` to `.env` and set your database and OAuth credentials.
   - If `.env.example` is missing, create a `.env` file with standard Laravel environment variables (DB, APP_KEY, etc.).
5. **Generate application key:**
   ```bash
   php artisan key:generate
   ```
6. **Run migrations:**
   ```bash
   php artisan migrate
   ```

## Running the Application
- **Development server:**
  ```bash
  php artisan serve
  ```
- **Vite dev server (for assets):**
  ```bash
  npm run dev
  ```
- **All-in-one (with queue and logs):**
  ```bash
  composer run dev
  ```

## API Overview
All API routes are prefixed with `/api` and require authentication (except for auth endpoints).

### Main Endpoints (see `routes/api.php`):
- **Projects:**
  - `POST /api/sadmin/projects` - Create project
  - `GET /api/sadmin/projects` - List projects
  - `PUT /api/sadmin/projects/{id}` - Edit project
  - `POST /api/sadmin/projects/{id}` - Delete project
  - ...and more (add/remove members, assign lead, etc.)
- **Tasks:**
  - `POST /api/sadmin/projects/{projectId}/tasks` - Add task
  - `PUT /api/sadmin/tasks/{taskId}` - Edit task
  - `PUT /api/sadmin/tasks/{taskId}/status` - Update status
  - `DELETE /api/sadmin/tasks/{taskId}` - Delete task
- **Users:**
  - `GET /api/sadmin/users` - List all users
  - `PUT /api/sadmin/profile/update` - Update profile
- **Activities:**
  - `GET /api/activities/recent` - Recent activities

> For full details, see the `routes/api.php` file.

## Project Structure
- `app/Http/Controllers/` - Main controllers (Auth, Sadmin)
- `app/Models/` - Eloquent models (User, Project, Task, Activities)
- `routes/api.php` - API route definitions
- `database/migrations/` - DB schema
- `public/` - Public assets

## Testing
- Run tests with:
  ```bash
  php artisan test
  ```

## License
This project is open-sourced under the MIT license.

## Screenshots

The Taskey UI (served by the frontend) looks like this:

![Taskey Kanban and Dashboard](../Taskey_frontend/public/assets/image.png)

## OpenAI-Powered Report Generation

Taskey now supports automatic project report generation using OpenAI GPT-4. The backend generates a detailed prompt including the project name, description, and a formatted list of tasks (with their status and description). The report is saved and returned via API.

### Endpoint
- `POST /api/sadmin/reports/generate`
  - **Body:** `{ project_id: number, title?: string }`
  - **Returns:** `{ success: boolean, report_id?: number, response?: string, error?: string }`

No need to send a prompt—the backend builds it for you using project and task data.
