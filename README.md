# HTWHT - Centralized Social Media Platform

A centralized social media platform based on Pixelfed, featuring:

- Google & Apple Sign-In authentication
- Blue verification badge system
- Earnings system (0.3$ per 1000 post views)
- Centralized architecture (no federation)

## Features

### Authentication
- Google OAuth integration
- Apple Sign In support
- Traditional email/password login

### Verification System
- Blue verification badge
- Verification request form with document upload
- Admin approval process

### Earnings System
- Earn $0.3 for every 1000 post views
- Detailed earnings dashboard
- View tracking and analytics

## Quick Setup

Run the automated setup script:

```bash
./setup-centralized.sh
```

Or follow the manual installation steps below.

## Manual Installation

This project is built on Laravel and requires PHP 8.1+, MySQL/PostgreSQL, and Redis.

### 1. Install Dependencies

```bash
composer install
```

### 2. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` file with:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

# Apple Sign In
APPLE_CLIENT_ID=your_apple_client_id
APPLE_CLIENT_SECRET=your_apple_client_secret
APPLE_KEY_ID=your_apple_key_id
APPLE_TEAM_ID=your_apple_team_id
APPLE_PRIVATE_KEY=your_apple_private_key

# Centralized Features
CENTRALIZED_MODE=true
EARNINGS_ENABLED=true
VERIFICATION_ENABLED=true
```

### 3. Database Setup

```bash
php artisan migrate
```

### 4. Storage Setup

```bash
php artisan storage:link
```

### 5. Queue Setup

For production, set up queue workers:

```bash
php artisan queue:work --daemon
```

Add to crontab:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Testing

Run the feature test script:

```bash
php test-centralized-features.php
```

## Features Documentation

See [CENTRALIZED_FEATURES.md](CENTRALIZED_FEATURES.md) for detailed documentation of all centralized features.

## API Endpoints

### Authentication
- `GET /auth/google` - Google OAuth login
- `GET /auth/apple` - Apple Sign In
- `GET /auth/{provider}/callback` - OAuth callback

### Verification
- `GET /settings/verification` - Verification request form
- `POST /settings/verification` - Submit verification request
- `GET /admin/verification` - Admin verification dashboard

### Earnings
- `GET /settings/earnings` - User earnings dashboard
- `GET /api/earnings` - Earnings API endpoint

## Admin Features

### Verification Management
- Review verification requests
- Approve/reject with feedback
- View verification statistics

### User Management
- View user earnings
- Manage verified users
- Analytics dashboard

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Queue Workers (Development)
```bash
php artisan queue:work
```

## Production Deployment

### 1. Optimize for Production
```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Set Up Supervisor
```bash
sudo cp /tmp/pixelfed-worker.conf /etc/supervisor/conf.d/
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start pixelfed-worker:*
```

### 3. Configure Web Server
Ensure your web server points to the `public` directory and has proper PHP configuration.

## Security

- All OAuth implementations follow security best practices
- File uploads are validated and stored securely
- Earnings calculations include anti-fraud measures
- User data is protected with proper encryption

## Support

For technical support:
1. Check the troubleshooting section in CENTRALIZED_FEATURES.md
2. Review application logs
3. Ensure all dependencies are properly installed

## License

This project is based on Pixelfed and maintains the same licensing terms.

See the installation guide for detailed setup instructions.
