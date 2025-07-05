# Taskey Backend

Taskey Backend is a comprehensive RESTful API built with Laravel 12, serving as the backend for the Taskey project management application. It provides robust endpoints for project, task, user, and activity management with advanced features like AI-powered reporting, real-time notifications, and role-based access control.

## Features

### Core Features
- **User Authentication & Authorization**
  - GitHub OAuth integration
  - Role-based access control (Master Admin, Super Admin, Admin, User)
  - JWT token authentication via Laravel Sanctum
  - User verification system

### Project Management
- **Project CRUD Operations**
  - Create, read, update, delete projects
  - Project member management (add/remove members)
  - Project lead assignment
  - Multi-company support

### Task Management
- **Advanced Task System**
  - Task CRUD operations
  - Task assignment and reassignment
  - Custom status management
  - Task chat functionality
  - "Need Help" status tracking
  - Task commit hash tracking

### AI-Powered Features
- **OpenAI Integration**
  - Automatic project report generation using GPT-4
  - Intelligent task analysis and reporting
  - Custom prompt generation based on project data

### Real-time Features
- **Notifications System**
  - Push notifications via Firebase Cloud Messaging (FCM)
  - In-app notifications
  - Real-time activity tracking
  - Comment system on activities

### User Management
- **Profile Management**
  - User profile updates
  - Role management
  - Company association
  - Profile image support

### Activity Tracking
- **Comprehensive Logging**
  - User activity tracking
  - Project activity monitoring
  - Task status changes
  - Comment system

## Tech Stack & Requirements

### Backend Stack
- **PHP** >= 8.2
- **Laravel** 12.x
- **MySQL/SQLite** (or other supported databases)
- **Redis** (for caching and queues)

### Frontend Assets
- **Node.js** & **npm**
- **Vite** (for asset building)
- **Tailwind CSS** 4.0

### External Services
- **GitHub OAuth** (for authentication)
- **OpenAI GPT-4** (for AI-powered reporting)
- **Firebase Cloud Messaging** (for push notifications)

### Development Tools
- **Composer** (PHP dependency management)
- **PHPUnit** (testing)
- **Laravel Pail** (log monitoring)
- **Concurrently** (running multiple processes)

## Installation

### 1. Clone the Repository
```bash
git clone <repo-url>
cd Taskey_backend
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration
```bash
# Copy environment configuration
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Environment Variables
Edit the `.env` file and configure:
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taskey
DB_USERNAME=your_username
DB_PASSWORD=your_password

# GitHub OAuth
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URI=your_redirect_uri

# OpenAI
OPENAI_API_KEY=your_openai_api_key

# Firebase
FIREBASE_CREDENTIALS=path/to/firebase-credentials.json
```

### 5. Database Setup
```bash
# Run migrations
php artisan migrate

# (Optional) Seed the database
php artisan db:seed
```

## Running the Application

### Development Server
```bash
# Start Laravel development server
php artisan serve

# Start Vite development server (for assets)
npm run dev

# Start queue worker (for background jobs)
php artisan queue:work

# Monitor logs
php artisan pail
```

### All-in-One Development
```bash
# Start all services simultaneously (server, queue, logs, vite)
composer run dev
```

### Production Build
```bash
# Build assets for production
npm run build

# Optimize Laravel for production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## API Documentation

All API routes are prefixed with `/api` and require authentication via Laravel Sanctum (except for auth endpoints).

### Authentication Routes
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `POST /api/auth/github` - GitHub OAuth authentication

### Project Management (`/api/sadmin/`)
#### Projects
- `POST /projects` - Create new project
- `GET /projects` - List all projects
- `GET /projects/{id}` - Get project details
- `PUT /projects/{id}` - Update project
- `DELETE /projects/{id}` - Delete project

#### Project Members
- `POST /projects/{id}/members` - Add members to project
- `POST /projects/{id}/remove-members` - Remove members from project
- `POST /projects/{id}/assign-lead` - Assign project lead
- `POST /projects/{id}/remove-lead` - Remove project lead

### Task Management
#### Tasks
- `POST /projects/{projectId}/tasks` - Create task in project
- `PUT /tasks/{taskId}` - Update task
- `DELETE /tasks/{taskId}` - Delete task
- `POST /tasks/{taskId}/assign` - Assign task to user
- `POST /tasks/{taskId}/remove-user` - Remove user from task
- `PUT /tasks/{taskId}/status` - Update task status
- `PUT /tasks/{taskId}/need-help` - Toggle need help status

#### Task Chat
- `GET /tasks/{taskId}/chat` - Get task chat messages
- `POST /tasks/{taskId}/join-chat` - Join task chat

### Status Management
- `GET /status` - Get all statuses
- `POST /status/create` - Create new status
- `DELETE /status/delete/{id}` - Delete status

### User Management
- `GET /users` - List all users
- `GET /verifiedUsers` - Get verified users
- `PUT /users/update/{id}` - Update user
- `DELETE /users/delete/{id}` - Delete user
- `PUT /profile/update` - Update profile

### Activity & Notifications
- `GET /activities/recent` - Get recent activities
- `GET /activities/all` - Get all activities
- `DELETE /activities/delete/{id}` - Delete activity
- `POST /activities/comment` - Comment on activity
- `GET /activities/comments/{id}` - Get activity comments

#### Notifications
- `POST /subscribe` - Subscribe to push notifications
- `POST /send-notification` - Send push notification
- `GET /notifications` - Get user notifications
- `POST /notifications/{id}/read` - Mark notification as read
- `DELETE /notifications/{id}` - Delete notification

### AI-Powered Reports
- `POST /reports/generate` - Generate AI report for project
  - **Body:** `{ "project_id": number, "title": "string" }`
  - **Returns:** Generated report with AI analysis
- `GET /reports` - List all reports
- `GET /reports/{projectId}` - Get project reports

### Dashboard Routes
- `GET /user/dashboard` - User dashboard data
- `GET /padmin/dashboard` - Project admin dashboard

> **Note:** For complete API documentation with request/response examples, see the `routes/api.php` file or use tools like Postman with the provided collection.

## Project Architecture

### Directory Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/              # Authentication controllers
│   │   ├── MasterAdmin/       # Master admin functionality
│   │   ├── Sadmin/            # Super admin functionality
│   │   │   ├── OpenAiController.php
│   │   │   ├── ReportsController.php
│   │   │   ├── TaskController.php
│   │   │   └── projectController.php
│   │   ├── Padmin/            # Project admin functionality
│   │   └── User/              # User-specific controllers
│   ├── Middleware/            # Custom middleware
│   └── Kernel.php
├── Models/                    # Eloquent models
│   ├── User.php
│   ├── Project.php
│   ├── Task.php
│   ├── activities.php
│   ├── Chat.php
│   ├── ChatMessage.php
│   ├── Notifications.php
│   └── Reports.php
└── Providers/                 # Service providers
```

### Key Models & Relationships
- **User Model**: Handles authentication, roles, and company associations
- **Project Model**: Manages projects with members and leads
- **Task Model**: Task management with status tracking and assignments
- **Activities Model**: Tracks all user actions and project activities
- **Chat Models**: Real-time chat functionality for tasks
- **Notifications Model**: In-app and push notifications

### Role-Based Access Control
- **Master Admin (0)**: Full system access
- **Super Admin (1)**: Company-wide management
- **Admin (2)**: Project and team management
- **User (3)**: Task execution and basic features

## Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage

# Run tests with verbose output
php artisan test --verbose
```

### Test Structure
```
tests/
├── Feature/          # Integration tests
├── Unit/            # Unit tests
└── TestCase.php     # Base test class
```

## Deployment

### Environment Setup
1. Set up your production environment variables
2. Configure your web server (Apache/Nginx)
3. Set up your database
4. Configure Redis for caching and queues
5. Set up SSL certificates

### Production Deployment
```bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Build assets
npm run build

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Start queue workers
php artisan queue:work --daemon
```

## Security

### Authentication & Authorization
- **JWT Tokens**: Secure token-based authentication via Laravel Sanctum
- **OAuth Integration**: GitHub OAuth for secure third-party authentication
- **Role-based Access**: Granular permissions based on user roles
- **API Rate Limiting**: Built-in rate limiting to prevent abuse

### Data Protection
- **Input Validation**: Comprehensive validation for all API endpoints
- **SQL Injection Prevention**: Eloquent ORM with prepared statements
- **XSS Protection**: Input sanitization and output encoding
- **CSRF Protection**: Token-based CSRF protection

### Best Practices
- Regular security updates
- Environment variable protection
- Secure API key management
- Database connection encryption

## Performance Optimization

### Caching Strategy
- **Redis Caching**: Fast data caching for frequently accessed data
- **Query Optimization**: Efficient database queries with proper indexing
- **API Response Caching**: Cached responses for repeated requests
- **Configuration Caching**: Optimized configuration loading

### Database Optimization
- **Proper Indexing**: Database indexes for performance-critical queries
- **Query Optimization**: Efficient Eloquent queries with eager loading
- **Database Migrations**: Version-controlled database schema changes

### Asset Management
- **Vite Integration**: Fast asset bundling and hot module replacement
- **Asset Compression**: Optimized CSS and JavaScript files
- **CDN Ready**: Prepared for content delivery network integration

## Troubleshooting

### Common Issues

#### Database Connection Issues
```bash
# Check database configuration
php artisan config:show database

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo()
```

#### Queue Issues
```bash
# Check queue status
php artisan queue:work --verbose

# Clear failed jobs
php artisan queue:clear
```

#### Permission Issues
```bash
# Fix storage permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

#### Cache Issues
```bash
# Clear all caches
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Debug Mode
```bash
# Enable debug mode (development only)
php artisan config:show app.debug

# Check application logs
php artisan pail
```

### Performance Monitoring
```bash
# Monitor queue performance
php artisan queue:monitor

# Check application status
php artisan about
```

## Changelog

### Version 2.0.0 (Current)
- **Added**: OpenAI GPT-4 integration for report generation
- **Added**: Firebase Cloud Messaging for push notifications
- **Added**: Real-time chat functionality for tasks
- **Added**: Advanced activity tracking and commenting
- **Added**: Custom status management
- **Improved**: Role-based access control
- **Improved**: API response structure and error handling
- **Updated**: Laravel 12 compatibility
- **Updated**: Tailwind CSS 4.0 integration

### Version 1.0.0
- **Initial Release**: Basic project and task management
- **Added**: GitHub OAuth authentication
- **Added**: User role management
- **Added**: Project CRUD operations
- **Added**: Task assignment and tracking

## Support

### Documentation
- [Laravel Documentation](https://laravel.com/docs)
- [API Documentation](./docs/api.md)
- [Deployment Guide](./docs/deployment.md)

### Community
- [GitHub Issues](https://github.com/your-repo/issues)
- [Discussions](https://github.com/your-repo/discussions)

### Commercial Support
For enterprise support and custom development, contact [your-email@domain.com](mailto:your-email@domain.com).
