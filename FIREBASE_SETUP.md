# Pix - Firebase Integration Guide

## نظرة عامة
تم تحويل مشروع Pix (المبني على Pixelfed) ليعمل بالكامل مع Firebase بدلاً من AWS. هذا الدليل يوضح كيفية إعداد Firebase وربطه مع التطبيق والتطبيق المحمول.

## 🔥 إعداد Firebase

### 1. إنشاء مشروع Firebase
1. اذهب إلى [Firebase Console](https://console.firebase.google.com/)
2. انقر على "Create a project" أو "إنشاء مشروع"
3. أدخل اسم المشروع (مثل: pix-social-media)
4. اختر إعدادات Google Analytics (اختياري)
5. انقر على "Create project"

### 2. تفعيل الخدمات المطلوبة

#### Firebase Storage
1. في Firebase Console، اذهب إلى "Storage"
2. انقر على "Get started"
3. اختر موقع التخزين (مثل: us-central1)
4. انقر على "Done"

#### Firestore Database
1. اذهب إلى "Firestore Database"
2. انقر على "Create database"
3. اختر "Start in production mode" أو "Start in test mode"
4. اختر موقع قاعدة البيانات
5. انقر على "Done"

#### Firebase Authentication (اختياري للمستقبل)
1. اذهب إلى "Authentication"
2. انقر على "Get started"
3. في تبويب "Sign-in method"، فعّل الطرق المطلوبة (Email/Password، Google، إلخ)

### 3. إنشاء Service Account
1. اذهب إلى "Project Settings" (⚙️)
2. انقر على تبويب "Service accounts"
3. انقر على "Generate new private key"
4. احفظ الملف باسم `firebase-credentials.json`
5. ضع الملف في مجلد المشروع الرئيسي

### 4. الحصول على معلومات المشروع
من "Project Settings" → "General":
- Project ID
- Web API Key
- Storage Bucket

## ⚙️ تكوين التطبيق

### 1. تحديث ملف .env
```env
# Firebase Configuration
FIREBASE_PROJECT_ID=your-project-id-here
FIREBASE_CREDENTIALS_PATH=/path/to/your/firebase-credentials.json
FIREBASE_DATABASE_URL=https://your-project-id-default-rtdb.firebaseio.com/
FIREBASE_STORAGE_DEFAULT_BUCKET=your-project-id.appspot.com

# تأكد من تعطيل AWS
AWS_ENABLED=false
S3_STORAGE=false

# إعدادات التخزين
FILESYSTEM_CLOUD=firebase
FILESYSTEM_DISK=firebase
```

### 2. تثبيت Dependencies
```bash
composer install
npm install
```

### 3. تشغيل Database Migrations
```bash
php artisan migrate
```

### 4. مسح Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 📱 ربط التطبيق المحمول

### للتطبيقات المبنية على Flutter

#### 1. إضافة Firebase إلى Flutter
```yaml
# pubspec.yaml
dependencies:
  firebase_core: ^2.24.2
  firebase_auth: ^4.15.3
  firebase_storage: ^11.5.6
  cloud_firestore: ^4.13.6
  http: ^1.1.0
```

#### 2. تكوين Firebase في Flutter
```dart
// lib/firebase_options.dart
import 'package:firebase_core/firebase_core.dart' show FirebaseOptions;
import 'package:flutter/foundation.dart'
    show defaultTargetPlatform, kIsWeb, TargetPlatform;

class DefaultFirebaseOptions {
  static FirebaseOptions get currentPlatform {
    if (kIsWeb) {
      return web;
    }
    switch (defaultTargetPlatform) {
      case TargetPlatform.android:
        return android;
      case TargetPlatform.iOS:
        return ios;
      default:
        throw UnsupportedError(
          'DefaultFirebaseOptions have not been configured for this platform.',
        );
    }
  }

  static const FirebaseOptions web = FirebaseOptions(
    apiKey: 'your-web-api-key',
    appId: 'your-app-id',
    messagingSenderId: 'your-sender-id',
    projectId: 'your-project-id',
    storageBucket: 'your-project-id.appspot.com',
  );

  static const FirebaseOptions android = FirebaseOptions(
    apiKey: 'your-android-api-key',
    appId: 'your-android-app-id',
    messagingSenderId: 'your-sender-id',
    projectId: 'your-project-id',
    storageBucket: 'your-project-id.appspot.com',
  );

  static const FirebaseOptions ios = FirebaseOptions(
    apiKey: 'your-ios-api-key',
    appId: 'your-ios-app-id',
    messagingSenderId: 'your-sender-id',
    projectId: 'your-project-id',
    storageBucket: 'your-project-id.appspot.com',
  );
}
```

#### 3. تهيئة Firebase في main.dart
```dart
import 'package:firebase_core/firebase_core.dart';
import 'firebase_options.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );
  runApp(MyApp());
}
```

#### 4. خدمة API للتواصل مع الخادم
```dart
// lib/services/api_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl = 'https://your-domain.com';
  
  // تسجيل الدخول
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/auth/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );
    
    return jsonDecode(response.body);
  }
  
  // رفع الصور
  static Future<String> uploadImage(File imageFile) async {
    var request = http.MultipartRequest(
      'POST',
      Uri.parse('$baseUrl/api/media/upload'),
    );
    
    request.files.add(
      await http.MultipartFile.fromPath('file', imageFile.path),
    );
    
    var response = await request.send();
    var responseData = await response.stream.toBytes();
    var result = jsonDecode(String.fromCharCodes(responseData));
    
    return result['url'];
  }
  
  // جلب المنشورات
  static Future<List<dynamic>> getPosts() async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/posts'),
      headers: {'Content-Type': 'application/json'},
    );
    
    return jsonDecode(response.body)['data'];
  }
}
```

### للتطبيقات المبنية على React Native

#### 1. تثبيت Firebase
```bash
npm install @react-native-firebase/app
npm install @react-native-firebase/auth
npm install @react-native-firebase/storage
npm install @react-native-firebase/firestore
```

#### 2. تكوين Firebase
```javascript
// firebase.config.js
import { initializeApp } from '@react-native-firebase/app';

const firebaseConfig = {
  apiKey: "your-api-key",
  authDomain: "your-project-id.firebaseapp.com",
  projectId: "your-project-id",
  storageBucket: "your-project-id.appspot.com",
  messagingSenderId: "your-sender-id",
  appId: "your-app-id"
};

const app = initializeApp(firebaseConfig);
export default app;
```

#### 3. خدمة API
```javascript
// services/ApiService.js
class ApiService {
  static baseUrl = 'https://your-domain.com';
  
  static async login(email, password) {
    const response = await fetch(`${this.baseUrl}/api/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email, password }),
    });
    
    return response.json();
  }
  
  static async uploadImage(imageUri) {
    const formData = new FormData();
    formData.append('file', {
      uri: imageUri,
      type: 'image/jpeg',
      name: 'image.jpg',
    });
    
    const response = await fetch(`${this.baseUrl}/api/media/upload`, {
      method: 'POST',
      body: formData,
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    
    return response.json();
  }
}

export default ApiService;
```

## 🔐 إعدادات الأمان

### Firebase Security Rules

#### Storage Rules
```javascript
// Firebase Storage Rules
rules_version = '2';
service firebase.storage {
  match /b/{bucket}/o {
    match /{allPaths=**} {
      allow read: if true;
      allow write: if request.auth != null;
    }
  }
}
```

#### Firestore Rules
```javascript
// Firestore Rules
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /{document=**} {
      allow read: if true;
      allow write: if request.auth != null;
    }
  }
}
```

## 🚀 API Endpoints للتطبيق المحمول

### Authentication
- `POST /api/auth/login` - تسجيل الدخول
- `POST /api/auth/register` - إنشاء حساب جديد
- `POST /api/auth/logout` - تسجيل الخروج
- `GET /api/auth/user` - معلومات المستخدم

### Posts
- `GET /api/posts` - جلب المنشورات
- `POST /api/posts` - إنشاء منشور جديد
- `GET /api/posts/{id}` - جلب منشور محدد
- `DELETE /api/posts/{id}` - حذف منشور

### Media
- `POST /api/media/upload` - رفع الصور/الفيديو
- `GET /api/media/{id}` - جلب ملف وسائط

### Profile
- `GET /api/profile` - معلومات الملف الشخصي
- `PUT /api/profile` - تحديث الملف الشخصي
- `POST /api/profile/avatar` - تحديث الصورة الشخصية

## 🔧 استكشاف الأخطاء

### مشاكل شائعة وحلولها

#### 1. خطأ في الاتصال بـ Firebase
```bash
# تأكد من صحة مسار ملف credentials
ls -la firebase-credentials.json

# تأكد من صحة متغيرات البيئة
php artisan config:show | grep FIREBASE
```

#### 2. مشاكل في رفع الملفات
- تأكد من تفعيل Firebase Storage
- تحقق من قواعد الأمان في Firebase Console
- تأكد من صحة bucket name

#### 3. مشاكل في قاعدة البيانات
```bash
# إعادة تشغيل migrations
php artisan migrate:fresh

# مسح cache
php artisan config:clear
```

## 📊 مراقبة الأداء

### Firebase Analytics
1. فعّل Google Analytics في Firebase Console
2. أضف tracking codes في التطبيق المحمول
3. راقب الإحصائيات من Firebase Console

### Performance Monitoring
1. فعّل Performance Monitoring في Firebase Console
2. أضف SDK في التطبيق المحمول
3. راقب أداء التطبيق

## 🔄 النسخ الاحتياطي

### إعداد النسخ الاحتياطي التلقائي
```bash
# في crontab
0 2 * * * cd /path/to/project && php artisan backup:run
```

### نسخ احتياطي لـ Firebase
- استخدم Firebase CLI لتصدير البيانات
- قم بإعداد نسخ احتياطي منتظم للملفات

## 📞 الدعم والمساعدة

للحصول على المساعدة:
1. راجع [Firebase Documentation](https://firebase.google.com/docs)
2. تحقق من [Laravel Documentation](https://laravel.com/docs)
3. راجع logs التطبيق: `storage/logs/laravel.log`

---

## ملاحظات مهمة

1. **الأمان**: تأكد من عدم رفع ملف `firebase-credentials.json` إلى Git
2. **الأداء**: استخدم CDN لتحسين سرعة تحميل الصور
3. **التكلفة**: راقب استخدام Firebase لتجنب التكاليف الزائدة
4. **النسخ الاحتياطي**: قم بعمل نسخ احتياطي منتظم للبيانات

تم تحويل المشروع بالكامل من AWS إلى Firebase مع الحفاظ على جميع الوظائف الأساسية.