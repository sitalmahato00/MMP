# MMP Mobile App API - Quick Start Guide

**Date:** June 5, 2026  
**For:** Development Team  

---

## 🚀 Quick Setup (5 minutes)

### 1. Configure Development Environment

```bash
# Copy environment file
cp .env.example .env

# Configure for local development
DB_HOST=localhost
DB_DATABASE=mmp_dev
DB_USERNAME=root
DB_PASSWORD=your_password

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate
```

### 2. Test Authentication Endpoint

```bash
# Start server
php artisan serve

# In another terminal, test login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student@example.com","password":"password"}'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Student Name",
      "email": "student@example.com",
      "role": "student"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

---

## 📚 API Endpoints

### All Endpoints Available At:

📄 **[docs/API_PROTECTED_ENDPOINTS.md](./API_PROTECTED_ENDPOINTS.md)** - Complete reference with 250+ pages

Quick links to main sections:
- Student Module
- Teacher Module  
- Parent Module
- HOD Module
- Alumni Module
- Authentication

---

## 🧪 Test Each Role

### Student Endpoints

```bash
TOKEN="1|abc123..." # from login response

# Get dashboard
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/student/dashboard

# Get attendance
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/student/attendance/summary

# Get marks
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/student/marks/summary

# Get assignments
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/student/assignments

# Get notices
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/student/notices
```

### Teacher Endpoints

```bash
# Get teacher dashboard
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/teacher/dashboard

# Mark attendance
curl -X POST http://localhost:8000/api/v1/teacher/attendance/mark \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "subject_id": 1,
    "status": "present",
    "date": "2026-06-05"
  }'
```

### Parent Endpoints

```bash
# Get children
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/parent/children

# Get child attendance
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/parent/child/1/attendance
```

---

## 🔐 Token Management

### Store Token (Frontend)

```kotlin
// Android example
val token = response.data.token
val sharedPrefs = context.getSharedPreferences("auth", Context.MODE_PRIVATE)
sharedPrefs.edit().putString("auth_token", token).apply()
```

### Use Token in Requests

```kotlin
// Add to every request
val token = sharedPrefs.getString("auth_token", null)
val request = originalRequest.newBuilder()
    .header("Authorization", "Bearer $token")
    .build()
```

### Logout

```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer $TOKEN"
```

---

## 📱 Android Integration

### 1. Setup Retrofit

```kotlin
// Add dependency
implementation("com.squareup.retrofit2:retrofit:2.11.0")
implementation("com.squareup.retrofit2:converter-gson:2.11.0")

// Create service
val retrofit = Retrofit.Builder()
    .baseUrl("http://10.0.2.2:8000/api")
    .addConverterFactory(GsonConverterFactory.create())
    .build()

val service = retrofit.create(StudentApiService::class.java)
```

### 2. Call Endpoints

```kotlin
// Interface
interface StudentApiService {
    @GET("v1/student/dashboard")
    suspend fun getDashboard(): Response<ApiResponse<StudentDashboard>>
}

// Usage
viewModelScope.launch {
    try {
        val response = service.getDashboard()
        if (response.isSuccessful) {
            val dashboard = response.body()?.data
            updateUI(dashboard)
        }
    } catch (e: Exception) {
        showError(e.message)
    }
}
```

**Full integration guide:** [docs/ANDROID_INTEGRATION_GUIDE.md](./ANDROID_INTEGRATION_GUIDE.md)

---

## 🐳 Docker Setup (Optional)

```bash
# Build and run with Docker
docker-compose up -d

# Run migrations inside container
docker-compose exec app php artisan migrate

# Access app
curl http://localhost:8000/api/v1/public/site-settings
```

---

## 📊 Response Format

All API responses follow this format:

```json
{
  "success": true,
  "message": "Success message",
  "data": {
    // Role-specific data
  }
}
```

Error response:

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

---

## ⚠️ Common Issues

### Issue: 401 Unauthorized
**Solution:** Token not provided or expired. Add token to headers.

```bash
# Wrong ❌
curl http://localhost:8000/api/v1/student/dashboard

# Correct ✅
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/student/dashboard
```

### Issue: 403 Forbidden
**Solution:** User role doesn't have access to endpoint.
- Check user role in database
- Verify token is for correct user

### Issue: 422 Validation Error
**Solution:** Invalid input data.
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 6 characters."]
  }
}
```

### Issue: Network Error (Development)
**Solution:** Can't reach API from Android emulator
```bash
# For Android emulator, use:
http://10.0.2.2:8000/api

# Or use your PC IP:
http://192.168.x.x:8000/api
```

---

## 📋 Project Structure

```
MMP/
├── app/Http/Controllers/Api/
│   ├── StudentController.php      ✅
│   ├── TeacherController.php      ✅
│   ├── ParentController.php       ✅
│   ├── HodController.php          ✅
│   ├── AlumniController.php       ✅
│   ├── AdminController.php        ✅
│   └── AuthController.php         ✅
├── routes/
│   └── api.php                    ✅ (200+ lines)
├── app/Http/Middleware/
│   └── RoleMiddleware.php         ✅
└── docs/
    ├── API_PROTECTED_ENDPOINTS.md ✅ (1200+ lines)
    ├── PRODUCTION_DEPLOYMENT_GUIDE.md ✅ (800+ lines)
    ├── ANDROID_INTEGRATION_GUIDE.md   ✅ (1000+ lines)
    └── README_API_IMPLEMENTATION.md   ✅
```

---

## ✅ Verification Steps

### Step 1: API Works
```bash
curl http://localhost:8000/api/v1/public/site-settings
# Should return data
```

### Step 2: Authentication Works
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student@example.com","password":"password"}'
# Should return token
```

### Step 3: Protected Endpoint Works
```bash
TOKEN="1|abc123..."
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/student/dashboard
# Should return student data
```

---

## 🚀 Deploy to Production

See: **[docs/PRODUCTION_DEPLOYMENT_GUIDE.md](./PRODUCTION_DEPLOYMENT_GUIDE.md)**

Quick checklist:
```
- [ ] Environment configured (.env)
- [ ] Database migrated
- [ ] Assets built (npm run build)
- [ ] Caches cleared
- [ ] SSL configured
- [ ] Backups enabled
- [ ] Monitoring setup
```

---

## 📖 Complete Documentation

| Document | Purpose | Read Time |
|----------|---------|-----------|
| [API_PROTECTED_ENDPOINTS.md](./API_PROTECTED_ENDPOINTS.md) | Complete endpoint reference | 30 min |
| [PRODUCTION_DEPLOYMENT_GUIDE.md](./PRODUCTION_DEPLOYMENT_GUIDE.md) | Deploy to production | 20 min |
| [ANDROID_INTEGRATION_GUIDE.md](./ANDROID_INTEGRATION_GUIDE.md) | Android app integration | 40 min |
| [README_API_IMPLEMENTATION.md](./README_API_IMPLEMENTATION.md) | Overview & summary | 10 min |

---

## 🔗 Useful Links

- Laravel Documentation: https://laravel.com/docs/11.x
- Sanctum (Authentication): https://laravel.com/docs/11.x/sanctum
- Retrofit (Android): https://square.github.io/retrofit/
- Firebase Messaging: https://firebase.google.com/docs/cloud-messaging

---

## ❓ Need Help?

1. **API Issues?** → Check `API_PROTECTED_ENDPOINTS.md` for endpoint details
2. **Deployment Issues?** → Check `PRODUCTION_DEPLOYMENT_GUIDE.md`
3. **Android Issues?** → Check `ANDROID_INTEGRATION_GUIDE.md`
4. **Database Issues?** → Check migrations and models

---

## 📝 Development Workflow

1. **Local Development** ✓
   - Setup .env with local database
   - Run migrations
   - Test endpoints with curl/Postman

2. **Android Development** ✓
   - Setup Retrofit with local API URL
   - Implement endpoints based on documentation
   - Test offline caching with Room

3. **Staging Testing** (coming)
   - Deploy to staging server
   - Run full test suite
   - Test mobile app against staging

4. **Production Deployment** (coming)
   - Follow deployment guide
   - Monitor and verify
   - Enable analytics & error tracking

---

## 🎯 Success Criteria

- [ ] All API endpoints responding
- [ ] Authentication working
- [ ] Role-based access working
- [ ] Error handling working
- [ ] Mobile app can login
- [ ] Mobile app can fetch data
- [ ] Offline caching working
- [ ] Push notifications working

---

**Generated:** June 5, 2026  
**Status:** Ready for Development  
**Next:** Start Android App Development
