#!/bin/bash

echo "🚀 Installing Pixelfed Centralized with AWS Integration"
echo "=================================================="

# Check if .env exists
if [ ! -f .env ]; then
    echo "📋 Creating .env file from example..."
    cp .env.example .env
    echo "✅ .env file created. Please configure your AWS settings!"
else
    echo "⚠️  .env file already exists"
fi

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
echo "📦 Installing Node.js dependencies..."
npm install

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Cache configuration
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo ""
echo "✅ Installation completed!"
echo ""
echo "📝 Next steps:"
echo "1. Configure your AWS settings in .env file"
echo "2. Set up your database connection"
echo "3. Configure your web server (Apache/Nginx)"
echo "4. Run: php artisan serve (for development)"
echo ""
echo "🔧 AWS Configuration required:"
echo "- AWS_ACCESS_KEY_ID"
echo "- AWS_SECRET_ACCESS_KEY"
echo "- AWS_BUCKET (for S3 storage)"
echo "- AWS_DEFAULT_REGION"
echo ""
echo "📚 See README-CENTRALIZED.md for detailed setup instructions"