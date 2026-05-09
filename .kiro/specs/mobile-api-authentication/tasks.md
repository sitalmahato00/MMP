# Implementation Plan: Mobile API Authentication

## Overview

This implementation plan breaks down the mobile API authentication feature into discrete coding tasks. The system will provide token-based authentication using Laravel Sanctum, enabling mobile applications to authenticate users via email/password or phone/OTP flows, with support for two-factor authentication.

The implementation leverages existing infrastructure (User model, OtpService, Spatie permissions) and adds new API endpoints with JSON-only responses suitable for mobile clients.

## Tasks

- [x] 1. Set up API infrastructure and middleware
  - Create ForceJsonResponse middleware to ensure all API responses are JSON
  - Configure middleware in `bootstrap/app.php` for all `/api/*` routes
  - Update `app/Exceptions/Handler.php` to return JSON for API exceptions
  - Configure Sanctum stateful domains in `config/sanctum.php`
  - Add API rate limiting configuration in `bootstrap/app.php`
  - _Requirements: 1.1, 1.2, 5.1, 5.2, 5.5, 6.1, 6.2, 6.3, 6.4, 6.5_

- [ ]* 1.1 Write unit tests for ForceJsonResponse middleware
  - Test that middleware forces Accept: application/json header
  - Test that API routes return JSON responses
  - Test that validation errors return JSON format
  - _Requirements: 6.1, 6.2, 6.4_

- [x] 2. Enhance User model with panel type determination
  - Add `getPanelType()` method to User model
  - Implement role-to-panel-type mapping logic (principal→admin, hod→hod, etc.)
  - Ensure method uses existing Spatie role checking
  - _Requirements: 3.2, 8.1, 8.2, 8.3, 8.4, 8.5, 8.6_

- [ ]* 2.1 Write unit tests for User model panel type method
  - Test getPanelType() returns 'admin' for principal role
  - Test getPanelType() returns 'hod' for hod role
  - Test getPanelType() returns 'teacher' for teacher role
  - Test getPanelType() returns 'student' for student role
  - Test getPanelType() returns 'parent' for parent role
  - Test getPanelType() returns 'alumni' for alumni role
  - Test getPanelType() returns 'guest' for users without roles
  - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6_

- [x] 3. Enhance LoginRequest validation for email support
  - Update `app/Http/Requests/LoginRequest.php` validation rules
  - Add email validation with `required_without:phone` rule
  - Add conditional validation logic in `withValidator()` method
  - Ensure either (email+password) or (phone+otp) or (phone+password) is provided
  - _Requirements: 2.1, 2.2, 12.3_

- [ ]* 3.1 Write unit tests for LoginRequest validation
  - Test email validation accepts valid email format
  - Test phone validation accepts valid phone format
  - Test validation requires either email or phone
  - Test validation requires either password or OTP
  - Test validation fails with invalid email format
  - Test validation fails with invalid phone format
  - Test validation passes with email and password
  - Test validation passes with phone and OTP
  - Test validation passes with phone and password
  - _Requirements: 2.1, 12.1, 12.3_

- [x] 4. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Implement AuthController login method
  - Create or update `app/Http/Controllers/Api/AuthController.php`
  - Implement `login(LoginRequest $request)` method
  - Add email-based user lookup logic
  - Add password verification using Hash::check()
  - Add active account check (`is_active` field)
  - Implement two-factor authentication detection and OTP sending
  - Add Sanctum token creation on successful authentication
  - Format user response with `formatUserResponse()` helper
  - Return JSON response with token and user data
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 3.1, 3.2, 3.3, 3.5, 3.6, 11.1, 11.2, 11.3, 11.4, 11.5_

- [ ]* 5.1 Write unit tests for login method
  - Test login succeeds with valid email and password
  - Test login succeeds with valid phone and password
  - Test login fails with invalid credentials
  - Test login fails with inactive account
  - Test login initiates 2FA when two_factor_enabled is true
  - Test login succeeds with valid email, password, and OTP (2FA)
  - Test login fails with invalid OTP
  - Test login returns API token on success
  - Test login returns user data with panel_type
  - Test login does not return password or remember_token
  - _Requirements: 2.2, 2.3, 2.4, 2.5, 3.1, 3.2, 3.5, 11.1, 11.2, 11.3, 11.4_

- [x] 6. Implement AuthController OTP methods
  - Implement `sendOtp(SendOtpRequest $request)` method
  - Integrate with existing OtpService to send OTP
  - Implement `verifyOtp(VerifyOtpRequest $request)` method
  - Add OTP verification logic using OtpService
  - Add Sanctum token creation on successful OTP verification
  - Return JSON responses for OTP operations
  - _Requirements: 2.1, 2.3, 11.1, 11.3, 11.4_

- [ ]* 6.1 Write unit tests for OTP methods
  - Test sendOtp sends OTP to valid phone number
  - Test sendOtp fails with invalid phone number
  - Test sendOtp respects rate limiting
  - Test verifyOtp succeeds with valid OTP
  - Test verifyOtp fails with invalid OTP
  - Test verifyOtp fails with expired OTP
  - Test verifyOtp returns API token on success
  - Test verifyOtp tracks failed attempts
  - _Requirements: 2.1, 2.3, 11.3, 11.4, 5.5_

- [x] 7. Implement AuthController logout method
  - Implement `logout(Request $request)` method
  - Add current token revocation using `$request->user()->currentAccessToken()->delete()`
  - Return JSON success response
  - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

- [ ]* 7.1 Write unit tests for logout method
  - Test logout revokes current token
  - Test logout returns success response
  - Test logout requires authentication
  - Test revoked token cannot be used for subsequent requests
  - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

- [x] 8. Implement helper methods in AuthController
  - Implement `getPanelType(User $user)` private method
  - Call User model's `getPanelType()` method
  - Implement `formatUserResponse(User $user)` private method
  - Format user data including id, name, email, phone, avatar_url, role, panel_type
  - Ensure sensitive fields (password, remember_token) are excluded
  - Load user relationships (student, teacher, parentProfile, alumnus) when needed
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 8.1, 8.2, 8.3, 8.4, 8.5, 8.6_

- [ ]* 8.1 Write unit tests for helper methods
  - Test getPanelType returns correct panel type for each role
  - Test formatUserResponse includes required fields
  - Test formatUserResponse excludes sensitive fields
  - Test formatUserResponse includes avatar_url when available
  - Test formatUserResponse includes correct role and panel_type
  - _Requirements: 3.1, 3.2, 3.5, 3.6, 8.6_

- [x] 9. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 10. Define API routes with middleware and rate limiting
  - Update `routes/api.php` with authentication routes
  - Add POST `/api/auth/login` route with `throttle:3,1` middleware
  - Add POST `/api/auth/send-otp` route with `throttle:3,1` middleware
  - Add POST `/api/auth/verify-otp` route with `throttle:3,1` middleware
  - Add POST `/api/auth/logout` route with `auth:sanctum` middleware
  - Add GET `/api/v1/user` route with `auth:sanctum` middleware
  - Apply ForceJsonResponse middleware to all API routes
  - _Requirements: 1.1, 1.2, 5.1, 5.2, 5.4, 5.5, 7.4, 9.1, 9.2, 9.3, 9.4, 10.1, 10.2_

- [x] 11. Implement user profile endpoint
  - Add `user()` method to AuthController or create UserController
  - Return authenticated user data using `formatUserResponse()`
  - Eager load user relationships (student, teacher, parentProfile, alumnus)
  - Return JSON response with user profile data
  - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

- [ ]* 11.1 Write unit tests for user profile endpoint
  - Test endpoint requires authentication
  - Test endpoint returns authenticated user data
  - Test endpoint includes user relationships
  - Test endpoint returns JSON format
  - Test endpoint returns 401 for unauthenticated requests
  - _Requirements: 10.2, 10.3, 10.4, 10.5_

- [x] 12. Implement comprehensive error handling
  - Update `app/Exceptions/Handler.php` render method
  - Add JSON error responses for ValidationException (422)
  - Add JSON error responses for AuthenticationException (401)
  - Add JSON error responses for AuthorizationException (403)
  - Add generic JSON error response for server errors (500)
  - Add error logging for server errors
  - Ensure consistent error response structure
  - _Requirements: 6.4, 6.5, 12.1, 12.2, 12.4, 12.5_

- [ ]* 12.1 Write unit tests for error handling
  - Test validation errors return 422 with JSON format
  - Test authentication errors return 401 with JSON format
  - Test authorization errors return 403 with JSON format
  - Test server errors return 500 with JSON format
  - Test error responses have consistent structure
  - _Requirements: 12.1, 12.2, 12.4, 12.5_

- [x] 13. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 14. Write integration tests for authentication flows
  - [x] 14.1 Write integration test for email/password authentication flow
    - Test complete login flow with valid email and password
    - Test token is created and returned
    - Test token can be used for authenticated requests
    - Test user data is returned correctly
    - _Requirements: 2.1, 2.2, 2.3, 3.1, 3.3, 4.1, 4.2_
  
  - [ ]* 14.2 Write integration test for 2FA authentication flow
    - Test login with 2FA enabled triggers OTP sending
    - Test login with valid OTP completes authentication
    - Test token is issued after successful 2FA
    - _Requirements: 11.1, 11.2, 11.3, 11.4_
  
  - [x] 14.3 Write integration test for phone/OTP authentication flow
    - Test sendOtp endpoint sends OTP to phone
    - Test verifyOtp endpoint validates OTP and issues token
    - Test token can be used for authenticated requests
    - _Requirements: 2.1, 2.3, 4.1, 4.2_
  
  - [ ]* 14.4 Write integration test for logout flow
    - Test logout revokes current token
    - Test revoked token returns 401 on subsequent requests
    - _Requirements: 7.1, 7.2, 7.5_
  
  - [ ]* 14.5 Write integration test for error scenarios
    - Test invalid credentials return 401
    - Test inactive account returns 403
    - Test rate limiting returns 429
    - Test validation errors return 422
    - _Requirements: 2.4, 2.5, 5.5, 12.1, 12.2, 12.4_

- [ ] 15. Write API endpoint tests
  - [x] 15.1 Write API tests for POST /api/auth/login
    - Test with valid email and password
    - Test with valid phone and password
    - Test with valid phone and OTP
    - Test with missing credentials
    - Test with invalid credentials
    - Test with inactive account
    - Test with 2FA enabled
    - Test rate limiting enforcement
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 5.5, 11.1, 11.2_
  
  - [ ]* 15.2 Write API tests for POST /api/auth/send-otp
    - Test with valid phone number
    - Test with invalid phone number
    - Test with non-existent user
    - Test rate limiting enforcement
    - _Requirements: 5.5_
  
  - [ ]* 15.3 Write API tests for POST /api/auth/verify-otp
    - Test with valid OTP
    - Test with invalid OTP
    - Test with expired OTP
    - Test attempt limiting (max 5 attempts)
    - _Requirements: 11.4_
  
  - [x] 15.4 Write API tests for POST /api/auth/logout
    - Test authenticated logout succeeds
    - Test unauthenticated logout returns 401
    - Test token is revoked after logout
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_
  
  - [x] 15.5 Write API tests for GET /api/v1/user
    - Test with valid token returns user data
    - Test with invalid token returns 401
    - Test with expired token returns 401
    - Test user relationships are loaded
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

- [x] 16. Final checkpoint - Ensure all tests pass
  - Run full test suite with `php artisan test`
  - Verify all authentication flows work correctly
  - Verify error handling returns proper JSON responses
  - Verify rate limiting is enforced
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- The implementation uses PHP (Laravel framework) with existing infrastructure
- Unit tests validate individual components in isolation
- Integration tests validate end-to-end authentication flows
- API tests verify HTTP endpoints directly
- All API responses must be in JSON format (no HTML/Blade views)
- Rate limiting is critical for security (3 attempts per minute for auth endpoints)
- Two-factor authentication is optional but must be supported when enabled
- Token-based authentication replaces session-based authentication for mobile clients
