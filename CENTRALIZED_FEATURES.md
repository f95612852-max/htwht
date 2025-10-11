# Centralized Pixelfed Features

This document outlines the new centralized features added to this Pixelfed instance.

## 🔐 Social Authentication

### Google OAuth
- Users can register and login using their Google accounts
- Automatic profile creation with Google profile information
- Secure OAuth 2.0 implementation

### Apple Sign In
- Users can register and login using Apple ID
- Privacy-focused authentication with Apple's privacy relay
- Support for Apple's private email relay system

### Configuration
Add these environment variables to your `.env` file:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback

# Apple Sign In
APPLE_CLIENT_ID=your_apple_client_id
APPLE_CLIENT_SECRET=your_apple_client_secret
APPLE_REDIRECT_URI=https://yourdomain.com/auth/apple/callback
APPLE_KEY_ID=your_apple_key_id
APPLE_TEAM_ID=your_apple_team_id
APPLE_PRIVATE_KEY=your_apple_private_key
```

## ✅ Verification System

### Blue Checkmark Verification
- Users can request verification through settings
- Admin approval system for verification requests
- Blue checkmark badge displayed across the platform

### Verification Process
1. User submits verification request with:
   - Full name
   - Contact information
   - Identity document (ID card, passport, etc.)
   - Reason for verification
   - Supporting attachments (optional)

2. Admin reviews the request
3. Admin approves or rejects with feedback
4. User receives email notification
5. Verified users get blue checkmark badge

### Features
- Document upload with validation
- Admin dashboard for managing requests
- Email notifications for status updates
- Badge display in profiles, comments, and notifications

## 💰 Earnings System

### Revenue Sharing
- Users earn $0.30 for every 1,000 unique post views
- Automatic earnings calculation every hour
- Detailed earnings dashboard and statistics

### View Tracking
- Unique view tracking per user per day
- IP-based deduplication to prevent abuse
- Real-time view counting with background processing

### Earnings Dashboard
- Total views and earnings overview
- Recent performance statistics
- Top performing posts analysis
- Earnings explanation and tips

### Configuration
```env
# Earnings System
EARNINGS_RATE_PER_THOUSAND=0.3
EARNINGS_ENABLED=true
EARNINGS_MINIMUM_PAYOUT=10.0
```

## 🚫 Federation Removal

### Disabled Features
- ActivityPub federation
- Remote follows
- Webfinger protocol
- Cross-instance communication
- Remote media fetching

### Benefits
- Improved performance
- Simplified architecture
- Better content control
- Enhanced security
- Centralized user experience

## 📊 Analytics & Tracking

### Post View Analytics
- Detailed view tracking for earnings calculation
- User engagement metrics
- Performance analytics
- Privacy-compliant tracking

### Features
- Real-time view counting
- Historical view data
- User engagement insights
- Earnings correlation

## 🛠️ Installation & Setup

### 1. Install Dependencies
```bash
composer install
composer require laravel/socialite socialiteproviders/apple socialiteproviders/google
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Configure Environment
Update your `.env` file with the required social authentication and earnings settings.

### 4. Set Up Scheduled Tasks
Add to your crontab:
```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Configure Queue Workers
```bash
php artisan queue:work --daemon
```

## 🔧 Artisan Commands

### Calculate Earnings
```bash
# Calculate earnings for all users
php artisan earnings:calculate

# Calculate with custom rate
php artisan earnings:calculate --rate=0.5
```

### Remove Federation Features
```bash
# Remove federation features (use with caution)
php artisan centralized:remove-federation --force
```

## 📱 Frontend Integration

### API Endpoints

#### Earnings API
```
GET /api/earnings - Get user earnings data
GET /api/earnings/stats - Get view statistics
```

#### Verification Status
```
GET /api/user/verification - Get verification status
POST /settings/verification - Submit verification request
```

### Verification Badge Component
```php
<x-verification-badge :user="$user" size="16px" />
```

### Blade Directives
```php
@verified($user)
    <span class="verified-badge">✓</span>
@endverified
```

## 🔒 Security Considerations

### Social Authentication
- Secure OAuth 2.0 implementation
- Token validation and refresh
- Account linking protection

### Verification System
- Document upload validation
- File type and size restrictions
- Admin-only approval process

### Earnings System
- View deduplication to prevent abuse
- IP-based tracking with privacy protection
- Secure earnings calculation

## 📈 Performance Optimizations

### Background Processing
- Asynchronous view tracking
- Queue-based earnings calculation
- Efficient database queries

### Caching
- View count caching
- Earnings data caching
- Verification status caching

## 🐛 Troubleshooting

### Common Issues

#### Social Authentication Not Working
1. Check OAuth credentials in `.env`
2. Verify redirect URLs match exactly
3. Ensure SSL is enabled for production

#### Earnings Not Calculating
1. Check if queue workers are running
2. Verify cron jobs are set up
3. Check earnings configuration in `.env`

#### Verification Requests Not Showing
1. Check file upload permissions
2. Verify storage disk configuration
3. Check admin user permissions

## 📞 Support

For technical support or questions about these centralized features, please:

1. Check the troubleshooting section above
2. Review the configuration files
3. Check application logs for errors
4. Ensure all dependencies are installed correctly

## 🔄 Updates and Maintenance

### Regular Tasks
- Monitor earnings calculations
- Review verification requests
- Update social authentication credentials
- Clean up old view logs (optional)

### Monitoring
- Check queue worker status
- Monitor earnings calculation accuracy
- Review verification request volume
- Track social authentication usage