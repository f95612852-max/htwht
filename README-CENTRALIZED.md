# Pix Centralized

نسخة مركزية من Pix مع دعم AWS وإزالة نظام الفيدرة.

## التغييرات الرئيسية

### تم إزالة:
- ✅ جميع مكونات ActivityPub
- ✅ نظام الفيدرة والخوادم المتعددة
- ✅ WebFinger وNodeInfo
- ✅ Remote media وRemote users
- ✅ Instance actors وFederation controllers
- ✅ جميع migrations المرتبطة بالخوادم الخارجية

### تم إضافة:
- ✅ دعم AWS S3 للتخزين
- ✅ دعم AWS SES للإيميل
- ✅ دعم AWS CloudFront للـ CDN
- ✅ إعدادات مركزية جديدة
- ✅ خدمة AWS متكاملة

## إعداد AWS

### 1. إعداد S3 للتخزين
```bash
AWS_S3_ENABLED=true
AWS_BUCKET=your-pix-bucket
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
```

### 2. إعداد SES للإيميل
```bash
AWS_SES_ENABLED=true
AWS_SES_REGION=us-east-1
MAIL_MAILER=ses
```

### 3. إعداد CloudFront للـ CDN
```bash
AWS_CLOUDFRONT_ENABLED=true
AWS_CLOUDFRONT_DOMAIN=your-cloudfront-domain.cloudfront.net
```

### 4. إعداد RDS لقاعدة البيانات
```bash
DB_CONNECTION=mysql
DB_HOST=your-rds-endpoint.amazonaws.com
DB_PORT=3306
DB_DATABASE=pix
DB_USERNAME=your-db-username
DB_PASSWORD=your-db-password
```

## التثبيت

1. نسخ الملفات:
```bash
cp .env.example .env
```

2. تعديل إعدادات AWS في ملف .env

3. تثبيت المتطلبات:
```bash
composer install
npm install
```

4. إنشاء مفتاح التطبيق:
```bash
php artisan key:generate
```

5. تشغيل migrations:
```bash
php artisan migrate
```

6. تحسين التطبيق:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## الميزات المتاحة

- ✅ رفع وعرض الصور
- ✅ إنشاء الألبومات
- ✅ نظام التعليقات والإعجابات
- ✅ البحث المحلي
- ✅ الملفات الشخصية
- ✅ الهاشتاغات
- ✅ القصص (Stories)
- ✅ نظام الإشعارات
- ✅ API للتطبيقات المحمولة

## الميزات المحذوفة

- ❌ الفيدرة مع خوادم أخرى
- ❌ ActivityPub protocol
- ❌ Remote follow
- ❌ WebFinger discovery
- ❌ Cross-instance interactions
- ❌ Instance actors
- ❌ Remote media caching

## الأمان

- جميع البيانات محفوظة على خادم واحد
- دعم AWS IAM للتحكم في الصلاحيات
- تشفير البيانات في S3
- SSL/TLS إجباري
- حماية من CSRF وXSS

## الأداء

- استخدام CloudFront لتسريع تحميل الصور
- تحسين قواعد البيانات
- Cache محسن للاستعلامات
- ضغط الصور تلقائياً

## الدعم

هذه نسخة معدلة من Pix للاستخدام المركزي مع AWS.