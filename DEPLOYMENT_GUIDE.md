# دليل النشر - منصة التواصل الاجتماعي المركزية

## نظرة عامة

تم تحويل هذا المشروع من Pix إلى منصة تواصل اجتماعي مركزية تتضمن:

- ✅ تسجيل الدخول بـ Google و Apple
- ✅ نظام التوثيق بالعلامة الزرقاء
- ✅ نظام الأرباح (0.3$ لكل 1000 مشاهدة)
- ✅ إزالة ميزات الفيدرالية
- ✅ تتبع المشاهدات والتحليلات

## متطلبات النظام

- PHP 8.1+
- MySQL 8.0+ أو PostgreSQL 13+
- Redis 6.0+
- Composer
- Node.js 16+ (للواجهة الأمامية)
- SSL Certificate (للإنتاج)

## خطوات النشر السريع

### 1. تشغيل السكريبت التلقائي

```bash
chmod +x setup-centralized.sh
./setup-centralized.sh
```

### 2. تكوين قاعدة البيانات

```bash
# إنشاء قاعدة البيانات
mysql -u root -p -e "CREATE DATABASE pix_centralized CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# تشغيل الهجرات
php artisan migrate
```

### 3. تكوين متغيرات البيئة

حدث ملف `.env`:

```env
# إعدادات التطبيق
APP_NAME="منصة التواصل المركزية"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# قاعدة البيانات
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pix_centralized
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

# الميزات المركزية
CENTRALIZED_MODE=true
EARNINGS_ENABLED=true
VERIFICATION_ENABLED=true
EARNINGS_RATE_PER_THOUSAND=0.3

# إعدادات الطوابير
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# إعدادات البريد الإلكتروني
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. تكوين خادم الويب

#### Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com;
    root /path/to/pix/public;

    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/private.key;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 100M;
}
```

### 5. تكوين Supervisor للطوابير

```ini
[program:pix-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/pix/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/pix/storage/logs/worker.log
stopwaitsecs=3600
```

### 6. تكوين Cron Jobs

```bash
# إضافة إلى crontab
* * * * * cd /path/to/pix && php artisan schedule:run >> /dev/null 2>&1
```

## إعداد OAuth

### Google OAuth Setup

1. اذهب إلى [Google Cloud Console](https://console.cloud.google.com/)
2. أنشئ مشروع جديد أو اختر مشروع موجود
3. فعل Google+ API
4. أنشئ OAuth 2.0 credentials
5. أضف redirect URI: `https://yourdomain.com/auth/google/callback`

### Apple Sign In Setup

1. اذهب إلى [Apple Developer Portal](https://developer.apple.com/)
2. أنشئ App ID جديد
3. فعل Sign In with Apple
4. أنشئ Service ID
5. أنشئ Private Key
6. أضف redirect URI: `https://yourdomain.com/auth/apple/callback`

## اختبار الميزات

### 1. اختبار التسجيل الاجتماعي

```bash
# اختبار Google OAuth
curl -I https://yourdomain.com/auth/google

# اختبار Apple Sign In
curl -I https://yourdomain.com/auth/apple
```

### 2. اختبار نظام التوثيق

1. سجل دخول كمستخدم عادي
2. اذهب إلى الإعدادات > التوثيق
3. املأ نموذج طلب التوثيق
4. تحقق من وصول الطلب لقاعدة البيانات

### 3. اختبار نظام الأرباح

```bash
# تشغيل حساب الأرباح يدوياً
php artisan earnings:calculate

# التحقق من جدولة المهام
php artisan schedule:list
```

## مراقبة النظام

### 1. مراقبة الطوابير

```bash
# التحقق من حالة العمال
sudo supervisorctl status pix-worker:*

# مراقبة الطوابير
php artisan queue:monitor redis
```

### 2. مراقبة الأداء

```bash
# مراقبة استخدام الذاكرة
php artisan horizon:status

# مراقبة قاعدة البيانات
php artisan db:monitor
```

### 3. مراقبة السجلات

```bash
# سجلات التطبيق
tail -f storage/logs/laravel.log

# سجلات العمال
tail -f storage/logs/worker.log

# سجلات خادم الويب
tail -f /var/log/nginx/access.log
```

## الصيانة الدورية

### يومياً

```bash
# تنظيف الملفات المؤقتة
php artisan cache:clear
php artisan view:clear

# تحسين قاعدة البيانات
php artisan optimize:clear
```

### أسبوعياً

```bash
# تنظيف سجلات المشاهدات القديمة
php artisan db:prune --model=App\\Models\\PostViewLog

# تحديث إحصائيات الأرباح
php artisan earnings:calculate --recalculate
```

### شهرياً

```bash
# نسخ احتياطي لقاعدة البيانات
mysqldump -u username -p pix_centralized > backup_$(date +%Y%m%d).sql

# تحديث التبعيات
composer update --no-dev
```

## استكشاف الأخطاء

### مشاكل شائعة

#### 1. OAuth لا يعمل

```bash
# التحقق من التكوين
php artisan config:cache
php artisan route:cache

# التحقق من الشهادات
openssl s_client -connect yourdomain.com:443
```

#### 2. الطوابير لا تعمل

```bash
# إعادة تشغيل العمال
sudo supervisorctl restart pix-worker:*

# التحقق من Redis
redis-cli ping
```

#### 3. الأرباح لا تحسب

```bash
# التحقق من المهام المجدولة
php artisan schedule:run

# تشغيل حساب الأرباح يدوياً
php artisan earnings:calculate --debug
```

## الأمان

### 1. تحديثات الأمان

```bash
# تحديث التبعيات
composer update

# فحص الثغرات الأمنية
composer audit
```

### 2. النسخ الاحتياطية

```bash
# نسخ احتياطي يومي
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p pix_centralized > /backups/db_$DATE.sql
tar -czf /backups/files_$DATE.tar.gz /path/to/pix/storage
```

### 3. مراقبة الأمان

```bash
# مراقبة محاولات الدخول المشبوهة
grep "authentication" storage/logs/laravel.log

# مراقبة رفع الملفات
ls -la storage/app/public/verification/documents/
```

## الدعم الفني

### سجلات مهمة

- `storage/logs/laravel.log` - سجلات التطبيق
- `storage/logs/worker.log` - سجلات العمال
- `/var/log/nginx/error.log` - أخطاء خادم الويب

### أوامر مفيدة

```bash
# إعادة تشغيل جميع الخدمات
sudo systemctl restart nginx php8.1-fpm redis-server mysql

# تنظيف شامل للكاش
php artisan optimize:clear && php artisan config:cache

# إعادة تشغيل العمال
sudo supervisorctl restart all
```

## الخلاصة

تم تحويل المشروع بنجاح إلى منصة تواصل اجتماعي مركزية مع جميع الميزات المطلوبة. تأكد من:

1. ✅ تكوين OAuth بشكل صحيح
2. ✅ تشغيل العمال والمهام المجدولة
3. ✅ مراقبة الأداء والأمان
4. ✅ إجراء النسخ الاحتياطية الدورية

للدعم الفني، راجع ملف `CENTRALIZED_FEATURES.md` للتفاصيل الكاملة.