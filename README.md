# Aimporo Marketplace - Laravel Backend

This is the backend API for the Aimporo Marketplace application, built with Laravel. It includes features such as audit logging, two-factor authentication, role-based permissions, and error tracking with Sentry.

## Features

- **Authentication**: Laravel Sanctum for API authentication
- **Two-Factor Authentication**: Using Laravel Fortify
- **Role-Based Access Control**: Using Spatie's Laravel Permission package
- **Audit Logging**: Track changes to models
- **Error Tracking**: Integration with Sentry
- **API Endpoints**: RESTful API for gigs, orders, messages, reviews, and more

## Requirements

- PHP 8.1 or higher
- Composer
- MySQL 5.7 or higher
- Node.js and NPM (for frontend)

## Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd laravel-app
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

4. Configure your `.env` file with your database credentials and other settings:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   SENTRY_LARAVEL_DSN=your_sentry_dsn
   ```

5. Run the setup command:
   ```bash
   php artisan app:setup
   ```

   This will:
   - Run migrations
   - Seed the database with roles and permissions
   - Clear cache
   - Generate application key
   - Optimize the application

6. Start the development server:
   ```bash
   php artisan serve
   ```

## Default Credentials

After running the setup command, you can log in with the following credentials:

- **Email**: admin@example.com
- **Password**: password

## Role-Based Access Control

The application includes the following roles:

- **Admin**: Full access to all features
- **Moderator**: Can moderate content and view audit logs
- **Seller**: Can create and manage gigs, complete orders
- **Buyer**: Can place orders, leave reviews
- **User**: Basic access (view gigs and reviews)

## API Routes

### Public Routes

- `POST /api/register`: Register a new user
- `POST /api/login`: Login and get access token
- `GET /api/gigs`: List all gigs
- `GET /api/gigs/{gig}`: View a specific gig
- `GET /api/reviews/gig/{gigId}`: Get reviews for a gig
- `GET /api/reviews/user/{userId}`: Get reviews for a user

### Protected Routes

All protected routes require authentication using a Bearer token.

#### User Routes

- `GET /api/user`: Get authenticated user details
- `POST /api/logout`: Logout (revoke token)

#### Gig Routes

- `POST /api/gigs`: Create a new gig (requires `create_gig` permission)
- `PUT /api/gigs/{gig}`: Update a gig (requires `edit_gig` permission)
- `DELETE /api/gigs/{gig}`: Delete a gig (requires `delete_gig` permission)

#### Order Routes

- `GET /api/orders`: List user's orders
- `POST /api/orders`: Create a new order
- `GET /api/orders/{order}`: View a specific order
- `PUT /api/orders/{order}`: Update an order
- `DELETE /api/orders/{order}`: Delete an order

#### Review Routes

- `POST /api/reviews/order/{orderId}`: Create a review (requires `create_review` permission)
- `PUT /api/reviews/{reviewId}`: Update a review (requires `edit_review` permission)
- `DELETE /api/reviews/{reviewId}`: Delete a review (requires `delete_review` permission)

#### Message Routes

- `GET /api/messages/conversations`: Get user's conversations
- `GET /api/messages/unread-count`: Get count of unread messages
- `GET /api/messages/order/{orderId}`: Get messages for an order
- `POST /api/messages/order/{orderId}`: Send a message
- `PUT /api/messages/{messageId}/read`: Mark a message as read

#### Audit Routes (Admin/Moderator Only)

- `GET /api/audits`: List all audit logs
- `GET /api/audits/user`: Get audit logs for current user
- `GET /api/audits/{model}/{id}`: Get audit logs for a specific model

#### Admin Routes (Admin Only)

- `GET /api/admin/users`: List all users
- `GET /api/admin/users/{user}`: View a specific user
- `PUT /api/admin/users/{user}/role`: Update a user's role
- `DELETE /api/admin/users/{user}`: Delete a user
- `GET /api/admin/stats`: Get system statistics

## Two-Factor Authentication

Two-factor authentication is implemented using Laravel Fortify. Users can enable 2FA from their account settings.

## Error Tracking with Sentry

The application is integrated with Sentry for error tracking. Make sure to set your Sentry DSN in the `.env` file.

## Security

- All sensitive information is stored in the `.env` file, which is not tracked by Git
- API routes are protected with appropriate middleware
- Role-based access control ensures users can only access what they're authorized to
- Two-factor authentication adds an extra layer of security

## License

This project is licensed under the MIT License.
