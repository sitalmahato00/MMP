# Requirements Document

## Introduction

This document specifies the requirements for implementing REST API endpoints that enable mobile applications (Android/Flutter) to authenticate users and access data from the existing Laravel College Management System (MMP CMS). The system will leverage the existing user authentication infrastructure, roles, and permissions while providing token-based authentication suitable for mobile clients.

## Glossary

- **Mobile_API**: The REST API endpoints designed for mobile application consumption
- **Auth_System**: The existing Laravel authentication system with Spatie permissions
- **Token_Manager**: Laravel Sanctum token management system
- **User_Repository**: The existing users table with roles (Student, Teacher, Parent, HOD, Admin)
- **Role_System**: Spatie Laravel Permission package managing roles and permissions
- **Mobile_Client**: Android or Flutter mobile application consuming the API
- **API_Token**: Secure authentication token issued by Laravel Sanctum
- **Panel_Type**: User's dashboard type determined by their role (student, teacher, parent, hod, admin)
- **Session_Auth**: Web-based session authentication (existing system)
- **Token_Auth**: Token-based authentication for mobile APIs

## Requirements

### Requirement 1: Token-Based Authentication

**User Story:** As a mobile app developer, I want token-based authentication instead of session-based authentication, so that mobile clients can authenticate securely without cookies or sessions.

#### Acceptance Criteria

1. THE Mobile_API SHALL use Laravel Sanctum for token management
2. THE Mobile_API SHALL NOT use session-based authentication for API requests
3. WHEN a valid API_Token is provided, THE Mobile_API SHALL authenticate the user without creating a session
4. THE Token_Manager SHALL generate unique API_Token for each successful authentication
5. THE API_Token SHALL remain valid until explicitly revoked or expired

### Requirement 2: User Authentication Endpoint

**User Story:** As a mobile app user, I want to log in using my email and password, so that I can access my college management system data from my mobile device.

#### Acceptance Criteria

1. THE Mobile_API SHALL provide a POST endpoint at `/api/auth/login` for authentication
2. WHEN valid email and password are provided, THE Mobile_API SHALL authenticate against the User_Repository
3. WHEN authentication succeeds, THE Mobile_API SHALL return an API_Token and user data in JSON format
4. IF authentication fails, THEN THE Mobile_API SHALL return an error message with HTTP 401 status
5. IF the user account is inactive, THEN THE Mobile_API SHALL reject authentication with an appropriate error message
6. THE Mobile_API SHALL validate that the user exists in the User_Repository before authentication
7. THE Mobile_API SHALL verify the password using the existing password hashing mechanism

### Requirement 3: User Data Response

**User Story:** As a mobile app developer, I want to receive user profile data including role information after successful login, so that the app can display the appropriate dashboard for each user type.

#### Acceptance Criteria

1. WHEN authentication succeeds, THE Mobile_API SHALL return user id, name, email, and role in the response
2. WHEN authentication succeeds, THE Mobile_API SHALL return Panel_Type based on the user's role
3. THE Mobile_API SHALL include the API_Token in the authentication response
4. THE Mobile_API SHALL return user avatar URL if available
5. THE Mobile_API SHALL NOT return sensitive data such as password or remember_token
6. THE Mobile_API SHALL return data in JSON format with consistent structure

### Requirement 4: Existing Data Integration

**User Story:** As a system administrator, I want the mobile API to use existing users, roles, and permissions, so that I don't need to maintain separate user databases or duplicate data.

#### Acceptance Criteria

1. THE Mobile_API SHALL authenticate users from the existing User_Repository
2. THE Mobile_API SHALL use the existing Role_System for role verification
3. THE Mobile_API SHALL NOT create duplicate user records or separate authentication tables
4. THE Mobile_API SHALL respect existing user relationships (student, teacher, parent profiles)
5. THE Mobile_API SHALL use the existing password verification mechanism from Auth_System

### Requirement 5: API Security and Protection

**User Story:** As a security administrator, I want API endpoints to be protected and accessible only to authenticated mobile users, so that unauthorized access is prevented.

#### Acceptance Criteria

1. THE Mobile_API SHALL protect authenticated endpoints with Sanctum middleware
2. WHEN an unauthenticated request is made to protected endpoints, THE Mobile_API SHALL return HTTP 401 status
3. WHEN an invalid or expired API_Token is provided, THE Mobile_API SHALL reject the request
4. THE Mobile_API SHALL validate API_Token on every protected endpoint request
5. THE Mobile_API SHALL implement rate limiting to prevent brute force attacks on authentication endpoints

### Requirement 6: JSON-Only Responses

**User Story:** As a mobile app developer, I want all API responses in JSON format without HTML or Blade views, so that the mobile app can parse responses consistently.

#### Acceptance Criteria

1. THE Mobile_API SHALL return all responses in JSON format
2. THE Mobile_API SHALL NOT render Blade views or HTML content
3. THE Mobile_API SHALL include appropriate Content-Type headers for JSON responses
4. WHEN an error occurs, THE Mobile_API SHALL return error details in JSON format
5. THE Mobile_API SHALL use consistent JSON structure for success and error responses

### Requirement 7: Token Revocation (Logout)

**User Story:** As a mobile app user, I want to log out from my mobile device, so that my authentication token is invalidated and my account is secure.

#### Acceptance Criteria

1. THE Mobile_API SHALL provide a POST endpoint at `/api/auth/logout` for token revocation
2. WHEN a logout request is made, THE Token_Manager SHALL revoke the current API_Token
3. WHEN a logout request is made, THE Mobile_API SHALL return a success confirmation in JSON format
4. THE Mobile_API SHALL require authentication to access the logout endpoint
5. WHEN a revoked API_Token is used, THE Mobile_API SHALL reject the request with HTTP 401 status

### Requirement 8: Role-Based Panel Type Determination

**User Story:** As a mobile app developer, I want to know which dashboard to display based on the user's role, so that each user sees the appropriate interface.

#### Acceptance Criteria

1. WHEN a user with role 'student' authenticates, THE Mobile_API SHALL return Panel_Type as 'student'
2. WHEN a user with role 'teacher' authenticates, THE Mobile_API SHALL return Panel_Type as 'teacher'
3. WHEN a user with role 'parent' authenticates, THE Mobile_API SHALL return Panel_Type as 'parent'
4. WHEN a user with role 'hod' authenticates, THE Mobile_API SHALL return Panel_Type as 'hod'
5. WHEN a user with role 'principal' authenticates, THE Mobile_API SHALL return Panel_Type as 'admin'
6. THE Mobile_API SHALL determine Panel_Type using the existing Role_System

### Requirement 9: Scalable API Structure

**User Story:** As a system architect, I want the API structure to support future mobile features, so that additional endpoints for attendance, marks, notices, and assignments can be added easily.

#### Acceptance Criteria

1. THE Mobile_API SHALL use versioned API routes (e.g., `/api/v1/`)
2. THE Mobile_API SHALL organize endpoints by resource type (auth, attendance, marks, notices)
3. THE Mobile_API SHALL use RESTful conventions for endpoint naming and HTTP methods
4. THE Mobile_API SHALL separate authentication routes from resource routes
5. THE Mobile_API SHALL provide a consistent response structure that can be extended for future features

### Requirement 10: User Profile Endpoint

**User Story:** As a mobile app developer, I want to retrieve the authenticated user's profile data, so that the app can display user information and verify the current session.

#### Acceptance Criteria

1. THE Mobile_API SHALL provide a GET endpoint at `/api/v1/user` for retrieving user profile
2. THE Mobile_API SHALL require authentication to access the user profile endpoint
3. WHEN a valid API_Token is provided, THE Mobile_API SHALL return the authenticated user's profile data
4. THE Mobile_API SHALL include user relationships (student, teacher, parent, alumni profiles) in the response
5. THE Mobile_API SHALL return data in JSON format with consistent structure

### Requirement 11: Two-Factor Authentication Handling

**User Story:** As a mobile app user with 2FA enabled, I want to complete two-factor authentication during mobile login, so that my account remains secure.

#### Acceptance Criteria

1. WHEN a user with two_factor_enabled attempts to authenticate, THE Mobile_API SHALL require OTP verification
2. IF two_factor_enabled is true and no OTP is provided, THEN THE Mobile_API SHALL return a response indicating OTP is required
3. WHEN valid credentials and valid OTP are provided, THE Mobile_API SHALL complete authentication and return API_Token
4. IF OTP verification fails, THEN THE Mobile_API SHALL return an error message with HTTP 401 status
5. THE Mobile_API SHALL use the existing OTP verification mechanism from Auth_System

### Requirement 12: Error Handling and Validation

**User Story:** As a mobile app developer, I want clear error messages and validation feedback, so that I can provide helpful information to users when authentication fails.

#### Acceptance Criteria

1. WHEN validation fails, THE Mobile_API SHALL return validation errors in JSON format with HTTP 422 status
2. WHEN authentication fails, THE Mobile_API SHALL return a descriptive error message
3. THE Mobile_API SHALL validate required fields (email, password) before processing authentication
4. THE Mobile_API SHALL return specific error messages for different failure scenarios (invalid credentials, inactive account, missing OTP)
5. THE Mobile_API SHALL use consistent error response structure across all endpoints

