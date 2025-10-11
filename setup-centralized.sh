#!/bin/bash

# Centralized Pixelfed Setup Script
# This script sets up the centralized features for Pixelfed

echo "🚀 Setting up Centralized Pixelfed Features..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Pixelfed root directory"
    exit 1
fi

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# Install additional packages for centralized features
echo "📦 Installing social authentication packages..."
composer require laravel/socialite socialiteproviders/apple socialiteproviders/google

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Clear and cache configuration
echo "⚙️ Clearing and caching configuration..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Create storage directories
echo "📁 Creating storage directories..."
mkdir -p storage/app/public/verification/documents
mkdir -p storage/logs

# Set proper permissions
echo "🔐 Setting proper permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Create symbolic link for storage
echo "🔗 Creating storage symbolic link..."
php artisan storage:link

# Remove federation features
echo "🚫 Removing federation features..."
php artisan centralized:remove-federation --force

# Generate application key if not exists
if grep -q "APP_KEY=$" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Set up queue tables if not exists
echo "📋 Setting up queue tables..."
php artisan queue:table
php artisan migrate --force

# Create admin user if needed
echo "👤 Creating admin user (if needed)..."
read -p "Do you want to create an admin user? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "Enter admin email: " admin_email
    read -s -p "Enter admin password: " admin_password
    echo
    php artisan tinker --execute="
        \$user = App\User::where('email', '$admin_email')->first();
        if (!\$user) {
            \$user = App\User::create([
                'name' => 'Admin',
                'username' => 'admin',
                'email' => '$admin_email',
                'password' => Hash::make('$admin_password'),
                'email_verified_at' => now(),
                'is_admin' => true,
            ]);
            \$profile = App\Profile::create([
                'user_id' => \$user->id,
                'username' => 'admin',
                'name' => 'Admin',
            ]);
            \$user->profile_id = \$profile->id;
            \$user->save();
            echo 'Admin user created successfully!';
        } else {
            echo 'Admin user already exists!';
        }
    "
fi

# Set up cron job
echo "⏰ Setting up cron job..."
echo "Add this line to your crontab (crontab -e):"
echo "* * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"

# Set up supervisor for queue workers
echo "👷 Setting up queue workers..."
cat > /tmp/pixelfed-worker.conf << EOF
[program:pixelfed-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $(pwd)/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=$(pwd)/storage/logs/worker.log
stopwaitsecs=3600
EOF

echo "Supervisor configuration created at /tmp/pixelfed-worker.conf"
echo "Copy it to /etc/supervisor/conf.d/ and run:"
echo "sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start pixelfed-worker:*"

# Environment variables check
echo "🔍 Checking environment variables..."
required_vars=("APP_KEY" "DB_CONNECTION" "DB_HOST" "DB_DATABASE" "DB_USERNAME")
missing_vars=()

for var in "${required_vars[@]}"; do
    if ! grep -q "^$var=" .env; then
        missing_vars+=("$var")
    fi
done

if [ ${#missing_vars[@]} -ne 0 ]; then
    echo "⚠️ Warning: Missing required environment variables:"
    printf '%s\n' "${missing_vars[@]}"
    echo "Please update your .env file"
fi

# Optional features setup
echo "🎯 Optional features setup..."

# Google OAuth setup
if ! grep -q "^GOOGLE_CLIENT_ID=" .env; then
    echo "📝 Google OAuth not configured. Add these to your .env:"
    echo "GOOGLE_CLIENT_ID=your_google_client_id"
    echo "GOOGLE_CLIENT_SECRET=your_google_client_secret"
fi

# Apple Sign In setup
if ! grep -q "^APPLE_CLIENT_ID=" .env; then
    echo "🍎 Apple Sign In not configured. Add these to your .env:"
    echo "APPLE_CLIENT_ID=your_apple_client_id"
    echo "APPLE_CLIENT_SECRET=your_apple_client_secret"
    echo "APPLE_KEY_ID=your_apple_key_id"
    echo "APPLE_TEAM_ID=your_apple_team_id"
    echo "APPLE_PRIVATE_KEY=your_apple_private_key"
fi

# Final checks
echo "✅ Setup completed!"
echo ""
echo "📋 Next steps:"
echo "1. Update your .env file with required credentials"
echo "2. Set up the cron job as shown above"
echo "3. Configure supervisor for queue workers"
echo "4. Test social authentication"
echo "5. Test verification system"
echo "6. Test earnings calculation"
echo ""
echo "🌐 Your centralized Pixelfed instance is ready!"
echo "Visit your site and check the new features in Settings."