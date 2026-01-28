# Backend README - User Management API

## Overview

This is a Symfony 6.3-based REST API for user management. The backend follows clean architecture principles with a repository pattern, service layer, and thin controllers.

## Prerequisites

- **PHP 8.1 or higher**
- **Composer** (PHP dependency manager)
- **SQLite** (default, or MySQL/PostgreSQL for production)
- **Symfony CLI** (optional, but recommended)

## Setup Instructions

### 1. Install Dependencies

```bash
cd backend
composer install
```

If you don't have Composer installed globally, you can use the included `composer.phar`:

```bash
php composer.phar install
```

### 2. Configure Environment

Create a `.env.local` file in the backend directory (or use the default `.env`):

```env
# Database Configuration
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# For MySQL/PostgreSQL (production):
# DATABASE_URL="mysql://user:password@127.0.0.1:3306/scoutclinical?serverVersion=8.0"
# DATABASE_URL="postgresql://user:password@127.0.0.1:5432/scoutclinical?serverVersion=13"
```

### 3. Create Database

```bash
# Create the database
php bin/console doctrine:database:create

# Create the schema
php bin/console doctrine:schema:create
```

**Note:** For SQLite, the database file will be created at `var/data.db` automatically.

### 4. (Optional) Load Fixtures

If you have fixtures defined:

```bash
php bin/console doctrine:fixtures:load
```

### 5. Start the Development Server

**Option A: Using PHP Built-in Server**
```bash
php -S localhost:8000 -t public
```

**Option B: Using Symfony CLI (Recommended)**
```bash
symfony server:start -d
```

The API will be available at `http://localhost:8000`

### 6. Verify Installation

Test the API endpoint:

```bash
curl http://localhost:8000/api/users
```

You should receive a JSON response (likely an empty array if no users exist).

## API Endpoints

### User Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/users` | List all users (with pagination) |
| `GET` | `/api/users?page=1&limit=10` | List users with pagination |
| `GET` | `/api/users/{id}` | Get a single user by ID |
| `POST` | `/api/users` | Create a new user |
| `PUT` | `/api/users/{id}` | Update an existing user |
| `DELETE` | `/api/users/{id}` | Delete a user |

### Request/Response Examples

**Create User:**
```bash
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{
    "firstName": "John",
    "lastName": "Doe",
    "email": "john.doe@example.com",
    "role": "user",
    "status": "active"
  }'
```

**Get All Users:**
```bash
curl http://localhost:8000/api/users?page=1&limit=10
```

## Project Structure

```
backend/
├── config/
│   ├── packages/
│   │   ├── cors.yaml          # CORS configuration
│   │   ├── doctrine.yaml      # Database configuration
│   │   ├── framework.yaml     # Symfony framework config
│   │   └── validator.yaml     # Validation configuration
│   ├── routes.yaml            # API routes
│   └── services.yaml          # Service container configuration
├── public/
│   └── index.php              # Application entry point
├── src/
│   ├── Controller/
│   │   └── UserController.php # API endpoints
│   ├── Entity/
│   │   └── User.php           # User entity (Doctrine)
│   ├── Repository/
│   │   └── UserRepository.php # Data access layer
│   ├── Service/
│   │   └── UserService.php    # Business logic layer
│   └── Kernel.php             # Symfony kernel
├── var/
│   └── data.db                # SQLite database (created on setup)
├── composer.json
└── README.md
```

## Architecture

### Design Patterns

- **Repository Pattern**: `UserRepository` handles all database operations
- **Service Layer**: `UserService` contains business logic and validation
- **Thin Controllers**: `UserController` delegates to services, handles HTTP concerns only

### Data Flow

```
HTTP Request → Controller → Service → Repository → Database
                ↓           ↓
            Validation  Business Logic
```

## Assumptions Made

1. **Database**: SQLite is used by default for simplicity and ease of setup. The system is designed to work with any Doctrine-supported database (MySQL, PostgreSQL, etc.) by changing the `DATABASE_URL`.

2. **CORS**: CORS is enabled for all origins (`*`) in development. In production, this should be restricted to specific frontend domains.

3. **Validation**: 
   - Email uniqueness is enforced at the database level (unique constraint)
   - Field validation (required, length, format) is handled in the service layer
   - Enum values (role, status) are validated but not strictly enforced at the database level

4. **Error Handling**: Errors return JSON responses with appropriate HTTP status codes. Detailed error messages are provided in development mode.

5. **Pagination**: Default pagination is set to 10 items per page. This can be adjusted via query parameters.

6. **Timestamps**: `createdAt` and `updatedAt` are managed automatically by Doctrine lifecycle callbacks.

7. **No Authentication**: The API does not include authentication/authorization. This would be added in a production environment.

8. **Soft Deletes**: Users are hard-deleted from the database. Soft deletes could be implemented if needed.

## Testing

### Manual Testing

Use tools like:
- **Postman** or **Insomnia** for API testing
- **curl** for command-line testing
- **Browser** for GET requests

### Unit Testing (Future)

```bash
# Run tests (when implemented)
php bin/phpunit
```

## Troubleshooting

### Database Connection Issues

If you encounter database errors:

1. Check that the database file exists: `var/data.db` (for SQLite)
2. Ensure write permissions on the `var/` directory
3. Verify `DATABASE_URL` in `.env` or `.env.local`

### Port Already in Use

If port 8000 is already in use:

```bash
# Use a different port
php -S localhost:8001 -t public
```

Then update the frontend proxy configuration accordingly.

### Composer Issues

If Composer installation fails:

```bash
# Clear Composer cache
composer clear-cache

# Update Composer
composer self-update
```

## What I Would Improve Given More Time

### 1. Testing
- **Unit Tests**: Add PHPUnit tests for services and repositories
- **Integration Tests**: Test API endpoints with a test database
- **Test Coverage**: Aim for 80%+ code coverage

### 2. Authentication & Authorization
- **JWT Authentication**: Implement token-based authentication
- **Role-Based Access Control (RBAC)**: Enforce permissions based on user roles
- **API Keys**: Support for API key authentication for service-to-service communication

### 3. Database Migrations
- **Doctrine Migrations**: Replace `doctrine:schema:create` with proper migrations
- **Migration Versioning**: Track database schema changes over time
- **Seed Data**: Add fixtures for development and testing

### 4. API Documentation
- **OpenAPI/Swagger**: Auto-generate API documentation
- **API Versioning**: Implement versioning (e.g., `/api/v1/users`)
- **Request/Response Examples**: Comprehensive documentation with examples

### 5. Error Handling & Logging
- **Structured Logging**: Use Monolog with proper log levels and contexts
- **Error Tracking**: Integrate with Sentry or similar error tracking service
- **Custom Exception Classes**: Domain-specific exceptions with proper error codes

### 6. Performance Optimization
- **Caching**: Implement Redis caching for frequently accessed data
- **Query Optimization**: Add database indexes for common queries
- **API Response Caching**: Cache GET requests where appropriate
- **Pagination Improvements**: Add total count, page metadata

### 7. Data Validation
- **DTOs (Data Transfer Objects)**: Use Symfony's serializer for request/response DTOs
- **Custom Validators**: More sophisticated validation rules
- **Validation Groups**: Different validation rules for create vs. update

### 8. Security Enhancements
- **Rate Limiting**: Prevent API abuse
- **Input Sanitization**: Additional security layers
- **SQL Injection Prevention**: Ensure all queries use parameter binding (already done with Doctrine)
- **XSS Protection**: Ensure proper content-type headers

### 9. Monitoring & Observability
- **Health Check Endpoint**: `/api/health` for monitoring
- **Metrics**: Track API usage, response times, error rates
- **APM Integration**: Application Performance Monitoring

### 10. Code Quality
- **Static Analysis**: Add PHPStan or Psalm
- **Code Style**: Enforce PSR-12 coding standards with PHP-CS-Fixer
- **Type Hints**: Add more strict type declarations
- **Documentation**: PHPDoc comments for all public methods

### 11. Advanced Features
- **Soft Deletes**: Implement soft delete functionality
- **Audit Logging**: Track who created/updated/deleted records
- **Bulk Operations**: Support for bulk create/update/delete
- **Search & Filtering**: Advanced search with filters (by role, status, date range)
- **Export Functionality**: CSV/Excel export of user data

### 12. Infrastructure
- **Docker Support**: Add Dockerfile and docker-compose.yml
- **CI/CD Pipeline**: Automated testing and deployment
- **Environment-Specific Configs**: Separate configs for dev/staging/production
- **Database Seeding**: Automated seed data for different environments

### 13. API Improvements
- **HATEOAS**: Add hypermedia links to responses
- **Field Selection**: Allow clients to specify which fields to return
- **Sorting**: Add sorting capabilities to list endpoints
- **Filtering**: Advanced filtering options

## Production Considerations

Before deploying to production:

1. **Environment Variables**: Move all sensitive data to environment variables
2. **Database**: Switch from SQLite to MySQL or PostgreSQL
3. **CORS**: Restrict CORS to specific frontend domains
4. **Error Messages**: Hide detailed error messages in production
5. **HTTPS**: Enforce HTTPS for all API requests
6. **Rate Limiting**: Implement rate limiting to prevent abuse
7. **Monitoring**: Set up logging and monitoring
8. **Backup Strategy**: Implement database backup procedures

## License

MIT

