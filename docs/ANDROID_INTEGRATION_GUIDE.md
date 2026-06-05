# MMP Android App - API Integration Guide

**Prepared for:** Prem Singh, Sital Mahato, Priti Dev, Rabin Sardar  
**Date:** June 5, 2026  
**Framework:** Kotlin, Retrofit 2, Hilt, Room Database  

---

## Table of Contents

1. [API Base URLs](#api-base-urls)
2. [Authentication Flow](#authentication-flow)
3. [Retrofit Setup](#retrofit-setup)
4. [Interceptors & Headers](#interceptors--headers)
5. [Token Management](#token-management)
6. [API Response Structure](#api-response-structure)
7. [Error Handling](#error-handling)
8. [Offline Caching with Room](#offline-caching-with-room)
9. [Push Notifications (FCM)](#push-notifications-fcm)
10. [Code Examples](#code-examples)
11. [Testing Checklist](#testing-checklist)

---

## API Base URLs

### Development
```
Base URL: http://10.0.2.2:8000/api  (Android Emulator)
or        http://your_pc_ip:8000/api
or        http://localhost:8000/api (if using device on same network)
```

### Staging
```
Base URL: https://staging.mmp.edu.np/api
```

### Production
```
Base URL: https://api.mmp.edu.np/api
```

**Environment Configuration:**

```kotlin
// BuildConfig or app_config.kt
object ApiConfig {
    const val BASE_URL = BuildConfig.API_BASE_URL
    // "http://10.0.2.2:8000/api" (dev)
    // "https://staging.mmp.edu.np/api" (staging)
    // "https://api.mmp.edu.np/api" (production)
}
```

---

## Authentication Flow

### 1. Login Request

**Request:**
```
POST /api/auth/login
Content-Type: application/json

{
  "email": "student@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "student@example.com",
      "role": "student",
      "avatar_url": "https://..."
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

### 2. Store Token Securely

```kotlin
// Using EncryptedSharedPreferences (AES-256-GCM)
val masterKey = MasterKey.Builder(context)
    .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
    .build()

val encryptedSharedPrefs = EncryptedSharedPreferences.create(
    context,
    "secret_shared_prefs",
    masterKey,
    EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
    EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
)

// Save token
encryptedSharedPrefs.edit().apply {
    putString("auth_token", token)
    putString("user_role", role)
    putLong("token_created_at", System.currentTimeMillis())
    apply()
}
```

### 3. Token Refresh

If token expires:

```
POST /api/auth/refresh-token
Authorization: Bearer {current_token}

Response: New token
```

### 4. Logout

```
POST /api/auth/logout
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## Retrofit Setup

### 1. Add Dependencies (build.gradle.kts)

```kotlin
dependencies {
    // Retrofit
    implementation("com.squareup.retrofit2:retrofit:2.11.0")
    implementation("com.squareup.retrofit2:converter-gson:2.11.0")
    implementation("com.squareup.okhttp3:okhttp:4.12.0")
    implementation("com.squareup.okhttp3:logging-interceptor:4.12.0")
    
    // Hilt DI
    implementation("com.google.dagger:hilt-android:2.50")
    kapt("com.google.dagger:hilt-compiler:2.50")
    
    // EncryptedSharedPreferences
    implementation("androidx.security:security-crypto:1.1.0-alpha06")
    
    // Room Database
    implementation("androidx.room:room-runtime:2.6.1")
    kapt("androidx.room:room-compiler:2.6.1")
    implementation("androidx.room:room-ktx:2.6.1")
}
```

### 2. Create Retrofit Instance (Hilt Module)

```kotlin
// di/NetworkModule.kt
@Module
@InstallIn(SingletonComponent::class)
object NetworkModule {
    
    @Provides
    @Singleton
    fun provideOkHttpClient(
        tokenManager: TokenManager,
        httpLoggingInterceptor: HttpLoggingInterceptor
    ): OkHttpClient {
        return OkHttpClient.Builder()
            .addInterceptor(tokenManager.authInterceptor())
            .addInterceptor(httpLoggingInterceptor)
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .writeTimeout(30, TimeUnit.SECONDS)
            .build()
    }
    
    @Provides
    fun provideHttpLoggingInterceptor(): HttpLoggingInterceptor {
        return HttpLoggingInterceptor().apply {
            level = if (BuildConfig.DEBUG) 
                HttpLoggingInterceptor.Level.BODY 
            else 
                HttpLoggingInterceptor.Level.NONE
        }
    }
    
    @Provides
    @Singleton
    fun provideGson(): Gson = Gson()
    
    @Provides
    @Singleton
    fun provideRetrofit(
        okHttpClient: OkHttpClient,
        gson: Gson
    ): Retrofit {
        return Retrofit.Builder()
            .baseUrl(ApiConfig.BASE_URL)
            .client(okHttpClient)
            .addConverterFactory(GsonConverterFactory.create(gson))
            .build()
    }
}
```

---

## Interceptors & Headers

### Auth Interceptor

```kotlin
// network/AuthInterceptor.kt
class AuthInterceptor(
    private val tokenManager: TokenManager
) : Interceptor {
    
    override fun intercept(chain: Interceptor.Chain): Response {
        var request = chain.request()
        
        // Add Authorization header if token exists
        val token = tokenManager.getToken()
        if (token != null) {
            request = request.newBuilder()
                .header("Authorization", "Bearer $token")
                .build()
        }
        
        // Add standard headers
        request = request.newBuilder()
            .header("Accept", "application/json")
            .header("Content-Type", "application/json")
            .build()
        
        var response = chain.proceed(request)
        
        // Handle 401 - Token expired
        if (response.code == 401) {
            // Try to refresh token
            val newToken = tokenManager.refreshToken()
            if (newToken != null) {
                // Retry request with new token
                request = chain.request().newBuilder()
                    .header("Authorization", "Bearer $newToken")
                    .build()
                response = chain.proceed(request)
            } else {
                // Logout user
                tokenManager.clearToken()
                // Navigate to login
            }
        }
        
        return response
    }
}
```

### Logging Interceptor

```kotlin
HttpLoggingInterceptor().apply {
    level = if (BuildConfig.DEBUG) 
        HttpLoggingInterceptor.Level.BODY 
    else 
        HttpLoggingInterceptor.Level.NONE
}
```

---

## Token Management

```kotlin
// TokenManager.kt
@Singleton
class TokenManager @Inject constructor(
    private val context: Context
) {
    private val encryptedPrefs by lazy {
        val masterKey = MasterKey.Builder(context)
            .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
            .build()
        
        EncryptedSharedPreferences.create(
            context,
            "secret_shared_prefs",
            masterKey,
            EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
            EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
        )
    }
    
    fun saveToken(token: String, userRole: String) {
        encryptedPrefs.edit().apply {
            putString("auth_token", token)
            putString("user_role", userRole)
            putLong("token_created_at", System.currentTimeMillis())
            apply()
        }
    }
    
    fun getToken(): String? = encryptedPrefs.getString("auth_token", null)
    
    fun getUserRole(): String? = encryptedPrefs.getString("user_role", null)
    
    fun isTokenExpired(): Boolean {
        val createdAt = encryptedPrefs.getLong("token_created_at", 0)
        val currentTime = System.currentTimeMillis()
        val tokenAge = (currentTime - createdAt) / 1000 / 60 // minutes
        
        // Consider expired if older than 24 hours
        return tokenAge > (24 * 60)
    }
    
    fun clearToken() {
        encryptedPrefs.edit().clear().apply()
    }
    
    fun authInterceptor() = Interceptor { chain ->
        val originalRequest = chain.request()
        
        val token = getToken()
        if (token != null) {
            val authenticatedRequest = originalRequest.newBuilder()
                .header("Authorization", "Bearer $token")
                .build()
            chain.proceed(authenticatedRequest)
        } else {
            chain.proceed(originalRequest)
        }
    }
    
    suspend fun refreshToken(): String? {
        return try {
            val apiService = Retrofit.Builder()
                .baseUrl(ApiConfig.BASE_URL)
                .build()
                .create(AuthApiService::class.java)
            
            val response = apiService.refreshToken("Bearer ${getToken()}")
            
            if (response.isSuccessful && response.body()?.success == true) {
                val newToken = response.body()?.data?.token
                newToken?.let { 
                    saveToken(it, getUserRole() ?: "user")
                    it
                }
            } else {
                null
            }
        } catch (e: Exception) {
            null
        }
    }
}
```

---

## API Response Structure

All API responses follow this structure:

```json
{
  "success": true/false,
  "message": "Response message",
  "data": {
    // Role-specific data
  },
  "errors": {
    "field": ["Error message"]  // Only on validation errors
  }
}
```

**Kotlin Data Classes:**

```kotlin
// models/ApiResponse.kt
data class ApiResponse<T>(
    val success: Boolean,
    val message: String,
    val data: T? = null,
    val errors: Map<String, List<String>>? = null
)

// For paginated responses
data class PaginatedResponse<T>(
    val success: Boolean,
    val data: List<T>,
    val pagination: PaginationInfo
)

data class PaginationInfo(
    val total: Int,
    val count: Int,
    val per_page: Int,
    val current_page: Int,
    val total_pages: Int
)

// Auth response specific
data class LoginResponse(
    val user: UserData,
    val token: String,
    val token_type: String
)

data class UserData(
    val id: Int,
    val name: String,
    val email: String,
    val role: String,
    val avatar_url: String? = null
)
```

---

## Error Handling

### Custom Exception Classes

```kotlin
// exceptions/ApiException.kt
sealed class ApiException : Exception() {
    data class NetworkException(val throwable: Throwable) : ApiException()
    data class ServerException(val code: Int, val message: String) : ApiException()
    data class ValidationException(val errors: Map<String, List<String>>) : ApiException()
    object UnauthorizedException : ApiException()
    object ForbiddenException : ApiException()
    object NotFoundException : ApiException()
    object ConflictException : ApiException()
    object RateLimitException : ApiException()
}

// Helper function to convert Response to exception
suspend inline fun <T> handleApiResponse(
    response: Response<ApiResponse<T>>
): T {
    return when {
        !response.isSuccessful -> {
            when (response.code()) {
                401 -> throw ApiException.UnauthorizedException
                403 -> throw ApiException.ForbiddenException
                404 -> throw ApiException.NotFoundException
                409 -> throw ApiException.ConflictException
                429 -> throw ApiException.RateLimitException
                422 -> {
                    val errorBody = response.errorBody()?.string()
                    val errorResponse = Gson().fromJson(errorBody, ApiResponse::class.java)
                    throw ApiException.ValidationException(errorResponse.errors ?: emptyMap())
                }
                else -> throw ApiException.ServerException(
                    response.code(),
                    response.message()
                )
            }
        }
        response.body() == null -> throw ApiException.ServerException(500, "Empty response body")
        !response.body()!!.success -> throw ApiException.ServerException(
            response.code(),
            response.body()!!.message
        )
        else -> response.body()!!.data ?: throw ApiException.ServerException(500, "No data in response")
    }
}
```

### In View Model / Repository

```kotlin
// ViewModel example
class StudentViewModel @Inject constructor(
    private val studentRepository: StudentRepository
) : ViewModel() {
    
    private val _uiState = MutableStateFlow<UiState>(UiState.Idle)
    val uiState = _uiState.asStateFlow()
    
    fun getDashboard() {
        viewModelScope.launch {
            try {
                _uiState.value = UiState.Loading
                val dashboard = studentRepository.getDashboard()
                _uiState.value = UiState.Success(dashboard)
            } catch (e: ApiException) {
                _uiState.value = UiState.Error(e.getUserFriendlyMessage())
            } catch (e: Exception) {
                _uiState.value = UiState.Error("An unexpected error occurred")
            }
        }
    }
    
    private fun ApiException.getUserFriendlyMessage(): String = when (this) {
        is ApiException.NetworkException -> "Network error. Please check your connection."
        is ApiException.UnauthorizedException -> "Session expired. Please login again."
        is ApiException.ForbiddenException -> "You don't have permission to access this resource."
        is ApiException.NotFoundException -> "Resource not found."
        is ApiException.RateLimitException -> "Too many requests. Please try again later."
        is ApiException.ValidationException -> "Invalid input. Please check your data."
        is ApiException.ServerException -> "Server error: $message"
    }
}

sealed class UiState {
    object Idle : UiState()
    object Loading : UiState()
    data class Success<T>(val data: T) : UiState()
    data class Error(val message: String) : UiState()
}
```

---

## Offline Caching with Room

### Database Setup

```kotlin
// database/AppDatabase.kt
@Database(
    entities = [
        UserEntity::class,
        StudentEntity::class,
        AttendanceEntity::class,
        MarkEntity::class,
        AssignmentEntity::class,
        NoticeEntity::class
    ],
    version = 1,
    exportSchema = false
)
abstract class AppDatabase : RoomDatabase() {
    abstract fun userDao(): UserDao
    abstract fun studentDao(): StudentDao
    abstract fun attendanceDao(): AttendanceDao
    // ... other DAOs
}

// Hilt Module
@Module
@InstallIn(SingletonComponent::class)
object DatabaseModule {
    @Provides
    @Singleton
    fun provideAppDatabase(context: Context): AppDatabase {
        return Room.databaseBuilder(
            context,
            AppDatabase::class.java,
            "mmp_database"
        ).fallbackToDestructiveMigration().build()
    }
}
```

### Entities

```kotlin
// database/entities/StudentEntity.kt
@Entity(tableName = "students")
data class StudentEntity(
    @PrimaryKey
    val id: Int,
    val name: String,
    val email: String,
    val roll_number: String?,
    val program: String?,
    val semester: Int?,
    @ColumnInfo(name = "cached_at")
    val cachedAt: Long = System.currentTimeMillis()
)

// Similar entities for other data models
```

### DAOs

```kotlin
// database/daos/StudentDao.kt
@Dao
interface StudentDao {
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(student: StudentEntity)
    
    @Query("SELECT * FROM students WHERE id = :id")
    suspend fun getStudent(id: Int): StudentEntity?
    
    @Query("DELETE FROM students")
    suspend fun deleteAll()
    
    @Query("SELECT * FROM students LIMIT 1")
    suspend fun getFirstStudent(): StudentEntity?
}
```

### Repository with Offline Support

```kotlin
// data/repository/StudentRepository.kt
@Singleton
class StudentRepository @Inject constructor(
    private val apiService: StudentApiService,
    private val database: AppDatabase,
    private val networkManager: NetworkManager
) {
    
    suspend fun getDashboard(): StudentDashboard {
        return if (networkManager.isConnected()) {
            try {
                val response = apiService.getDashboard()
                val dashboard = handleApiResponse(response)
                
                // Cache in Room
                cacheStudentData(dashboard)
                dashboard
            } catch (e: Exception) {
                // Fallback to cached data
                getCachedDashboard() ?: throw e
            }
        } else {
            getCachedDashboard() ?: throw Exception("No internet and no cached data")
        }
    }
    
    private suspend fun cacheStudentData(dashboard: StudentDashboard) {
        // Save to Room
        database.studentDao().insert(dashboard.toEntity())
    }
    
    private suspend fun getCachedDashboard(): StudentDashboard? {
        val studentEntity = database.studentDao().getFirstStudent()
        return studentEntity?.toDomain()
    }
}
```

---

## Push Notifications (FCM)

### 1. Setup FCM

**Step 1:** Add Firebase to `build.gradle.kts`

```kotlin
implementation(platform("com.google.firebase:firebase-bom:32.x.x"))
implementation("com.google.firebase:firebase-messaging")
```

**Step 2:** Create Firebase Service

```kotlin
// fcm/MmpFirebaseMessagingService.kt
class MmpFirebaseMessagingService : FirebaseMessagingService() {
    
    override fun onNewToken(token: String) {
        super.onNewToken(token)
        
        // Send token to backend API
        GlobalScope.launch(Dispatchers.IO) {
            try {
                // Call API endpoint to store FCM token
                val apiService = Retrofit.Builder()
                    .baseUrl(ApiConfig.BASE_URL)
                    .build()
                    .create(UserApiService::class.java)
                
                apiService.updateFcmToken(FCMTokenRequest(token))
            } catch (e: Exception) {
                e.printStackTrace()
            }
        }
    }
    
    override fun onMessageReceived(remoteMessage: RemoteMessage) {
        super.onMessageReceived(remoteMessage)
        
        // Handle notification
        remoteMessage.notification?.let { notification ->
            val title = notification.title ?: "MMP Notification"
            val body = notification.body ?: ""
            val notificationId = System.currentTimeMillis().toInt()
            
            showNotification(title, body, notificationId)
        }
        
        // Handle data payload
        remoteMessage.data.let { data ->
            val type = data["type"]
            val screen = data["screen"]
            val id = data["id"]
            
            // Route to specific screen based on notification type
            handleNotificationNavigation(type, screen, id)
        }
    }
    
    private fun showNotification(title: String, message: String, notificationId: Int) {
        val intent = Intent(this, MainActivity::class.java)
        val pendingIntent = PendingIntent.getActivity(
            this,
            0,
            intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
        
        val notification = NotificationCompat.Builder(this, CHANNEL_ID)
            .setContentTitle(title)
            .setContentText(message)
            .setSmallIcon(R.drawable.ic_notification)
            .setContentIntent(pendingIntent)
            .setAutoCancel(true)
            .build()
        
        NotificationManagerCompat.from(this).notify(notificationId, notification)
    }
    
    private fun handleNotificationNavigation(type: String?, screen: String?, id: String?) {
        // Handle deep linking based on notification type
        when (type) {
            "attendance" -> {
                // Navigate to attendance screen
            }
            "marks" -> {
                // Navigate to marks screen
            }
            "assignment" -> {
                // Navigate to assignments screen
            }
            "notice" -> {
                // Navigate to notices screen
            }
        }
    }
    
    companion object {
        private const val CHANNEL_ID = "mmp_notifications"
    }
}
```

**Step 3:** Create Notification Channel (Android 8+)

```kotlin
// In Application or MainActivity
private fun createNotificationChannel() {
    val channel = NotificationChannel(
        "mmp_notifications",
        "MMP Notifications",
        NotificationManager.IMPORTANCE_HIGH
    ).apply {
        description = "Notifications for MMP academic portal"
    }
    
    val notificationManager: NotificationManager =
        getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
    
    notificationManager.createNotificationChannel(channel)
}
```

### 2. Send FCM Token to Backend

```kotlin
// API endpoint
data class FCMTokenRequest(val fcm_token: String)

// API Service
@POST("user/fcm-token")
suspend fun updateFcmToken(
    @Body request: FCMTokenRequest
): Response<ApiResponse<Unit>>
```

---

## Code Examples

### Example 1: Student Login

```kotlin
// ui/screens/LoginScreen.kt
@Composable
fun LoginScreen(
    viewModel: LoginViewModel = hiltViewModel(),
    onLoginSuccess: () -> Unit
) {
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    
    val uiState by viewModel.uiState.collectAsState()
    
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(16.dp),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        TextField(
            value = email,
            onValueChange = { email = it },
            label = { Text("Email") },
            modifier = Modifier.fillMaxWidth()
        )
        
        TextField(
            value = password,
            onValueChange = { password = it },
            label = { Text("Password") },
            visualTransformation = PasswordVisualTransformation(),
            modifier = Modifier.fillMaxWidth()
        )
        
        Button(
            onClick = { viewModel.login(email, password) },
            modifier = Modifier
                .fillMaxWidth()
                .padding(top = 16.dp)
        ) {
            Text("Login")
        }
        
        when (uiState) {
            UiState.Loading -> {
                CircularProgressIndicator()
            }
            is UiState.Error -> {
                Text(
                    (uiState as UiState.Error).message,
                    color = Color.Red
                )
            }
            is UiState.Success -> {
                onLoginSuccess()
            }
            else -> {}
        }
    }
}

// ViewModel
@HiltViewModel
class LoginViewModel @Inject constructor(
    private val authRepository: AuthRepository
) : ViewModel() {
    
    private val _uiState = MutableStateFlow<UiState>(UiState.Idle)
    val uiState = _uiState.asStateFlow()
    
    fun login(email: String, password: String) {
        viewModelScope.launch {
            try {
                _uiState.value = UiState.Loading
                val response = authRepository.login(email, password)
                _uiState.value = UiState.Success(response)
            } catch (e: ApiException) {
                _uiState.value = UiState.Error(e.message ?: "Login failed")
            }
        }
    }
}
```

### Example 2: Fetch Student Dashboard

```kotlin
// Repository
@Singleton
class StudentRepository @Inject constructor(
    private val apiService: StudentApiService,
    private val database: AppDatabase,
    private val networkManager: NetworkManager
) {
    
    suspend fun getDashboard(): StudentDashboard = withContext(Dispatchers.IO) {
        return@withContext try {
            val response = apiService.getDashboard()
            val dashboard = handleApiResponse(response)
            
            // Cache data
            database.studentDao().insert(dashboard.toEntity())
            dashboard
        } catch (e: Exception) {
            // Try cache
            database.studentDao().getFirstStudent()?.toDomain()
                ?: throw e
        }
    }
}

// UI Layer
@HiltViewModel
class StudentDashboardViewModel @Inject constructor(
    private val repository: StudentRepository
) : ViewModel() {
    
    private val _dashboard = MutableStateFlow<StudentDashboard?>(null)
    val dashboard = _dashboard.asStateFlow()
    
    private val _error = MutableStateFlow<String?>(null)
    val error = _error.asStateFlow()
    
    private val _isLoading = MutableStateFlow(false)
    val isLoading = _isLoading.asStateFlow()
    
    fun loadDashboard() {
        viewModelScope.launch {
            _isLoading.value = true
            try {
                val data = repository.getDashboard()
                _dashboard.value = data
                _error.value = null
            } catch (e: Exception) {
                _error.value = e.message ?: "Unknown error"
            } finally {
                _isLoading.value = false
            }
        }
    }
}

// Composable
@Composable
fun StudentDashboardScreen(
    viewModel: StudentDashboardViewModel = hiltViewModel()
) {
    val dashboard by viewModel.dashboard.collectAsState()
    val isLoading by viewModel.isLoading.collectAsState()
    val error by viewModel.error.collectAsState()
    
    LaunchedEffect(Unit) {
        viewModel.loadDashboard()
    }
    
    when {
        isLoading -> {
            CircularProgressIndicator()
        }
        error != null -> {
            Text("Error: $error", color = Color.Red)
        }
        dashboard != null -> {
            DashboardContent(dashboard!!)
        }
    }
}

@Composable
fun DashboardContent(dashboard: StudentDashboard) {
    LazyColumn(
        modifier = Modifier.fillMaxSize(),
        contentPadding = PaddingValues(16.dp)
    ) {
        item {
            Text(
                "Welcome, ${dashboard.student_name}",
                style = MaterialTheme.typography.headlineSmall
            )
        }
        
        item {
            KpiCard(
                title = "Attendance",
                value = "${dashboard.kpi_cards.attendance_percentage}%",
                icon = Icons.Default.Info
            )
        }
        
        item {
            KpiCard(
                title = "Average Marks",
                value = dashboard.kpi_cards.average_marks.toString(),
                icon = Icons.Default.Edit
            )
        }
        
        item {
            KpiCard(
                title = "Pending Assignments",
                value = dashboard.kpi_cards.pending_assignments.toString(),
                icon = Icons.Default.Edit
            )
        }
    }
}
```

---

## Testing Checklist

### Unit Tests
- [ ] Token manager tests
- [ ] API response parsing
- [ ] Error handling
- [ ] Database CRUD operations

### Integration Tests
- [ ] Login endpoint
- [ ] Token refresh
- [ ] Logout
- [ ] Each role's main endpoint

### UI/E2E Tests
- [ ] Login flow
- [ ] Dashboard loading
- [ ] Offline data display
- [ ] Push notification handling
- [ ] Navigation between roles

### Performance Tests
- [ ] API response time < 500ms
- [ ] Database queries < 100ms
- [ ] Offline cache loads instantly
- [ ] Memory usage < 200MB

### Security Tests
- [ ] Token stored securely
- [ ] SSL/TLS working
- [ ] No credentials in logs
- [ ] Proper error messages (no sensitive info)

### Device Tests
- [ ] Min SDK API 24 works
- [ ] Target SDK API 34 works
- [ ] Various screen sizes
- [ ] Low network conditions
- [ ] Offline scenarios

---

## Deployment Checklist

Before releasing to Play Store:
- [ ] All endpoints tested against production API
- [ ] Production URLs configured
- [ ] Proper error handling implemented
- [ ] User manual prepared
- [ ] Privacy policy created
- [ ] App signing configured
- [ ] ProGuard/R8 rules configured
- [ ] Crash reporting enabled (Firebase Crashlytics)
- [ ] Analytics implemented
- [ ] App tested on multiple devices

---

## Support & Debugging

**Enable Detailed Logging:**

```kotlin
// In debug build
if (BuildConfig.DEBUG) {
    okHttpClient.addInterceptor(HttpLoggingInterceptor().apply {
        level = HttpLoggingInterceptor.Level.BODY
    })
}
```

**Common Issues:**

| Issue | Solution |
|-------|----------|
| 401 Unauthorized | Token expired - implement refresh logic |
| 403 Forbidden | User role doesn't have access |
| Network timeout | Increase timeout, check network |
| 422 Validation | Check request JSON format |
| Empty response | Check API response format |
| SSL error | Use production URL with valid SSL |

**Contact Support:**
- Backend API Issues: Backend team
- Deployment Issues: DevOps team
- Design/UI Issues: UI/UX team

---

**Last Updated:** June 5, 2026  
**API Version:** v1.0  
**Android Min SDK:** API 24 (Android 7.0)
