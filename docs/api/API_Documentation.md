# DFCMS API Documentation

## 🚀 RESTful API Reference

### 📋 Table of Contents
- [Authentication](#-authentication)
- [Base URL](#-base-url)
- [Endpoints](#-endpoints)
- [Error Handling](#-error-handling)
- [Rate Limiting](#-rate-limiting)
- [Examples](#-examples)

---

## 🔐 Authentication

### JWT Token Authentication
All API requests require authentication using JWT tokens.

#### **Request Token**
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```

#### **Response**
```json
{
    "success": true,
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "Bearer",
        "expires_in": 3600,
        "user": {
            "id": 1,
            "full_name": "John Doe",
            "email": "john@example.com",
            "role": "student"
        }
    }
}
```

#### **Use Token**
```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

---

## 🌐 Base URL

```
Production: https://api.dfcms.university.edu/v1
Development: http://localhost/dfcms/api/v1
```

---

## 📚 Endpoints

### 👤 Authentication

#### **Login**
```http
POST /auth/login
```

**Request Body:**
```json
{
    "email": "string",
    "password": "string"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "access_token": "string",
        "refresh_token": "string",
        "user": {
            "id": 1,
            "full_name": "string",
            "email": "string",
            "role": "student|cr|teacher|lab_assistant|hod"
        }
    }
}
```

#### **Refresh Token**
```http
POST /auth/refresh
```

#### **Logout**
```http
POST /auth/logout
```

#### **Request Password Reset**
```http
POST /auth/password/reset
```

---

### 📋 Complaints

#### **Get Complaints**
```http
GET /complaints?page=1&limit=10&status=Pending&category=Academic
```

**Response:**
```json
{
    "success": true,
    "data": {
        "complaints": [
            {
                "id": 1,
                "student_id": 1,
                "category": "Academic",
                "priority": "High",
                "message": "Issue with lab equipment",
                "status": "Pending",
                "created_at": "2024-01-15T10:30:00Z",
                "updated_at": "2024-01-15T10:30:00Z",
                "student": {
                    "id": 1,
                    "full_name": "John Doe",
                    "email": "john@example.com"
                },
                "assigned_to": null,
                "current_handler_role": "cr"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 5,
            "total_items": 50,
            "items_per_page": 10
        }
    }
}
```

#### **Get Complaint by ID**
```http
GET /complaints/{id}
```

#### **Create Complaint**
```http
POST /complaints
```

**Request Body:**
```json
{
    "category": "Academic",
    "priority": "High",
    "message": "Issue with lab equipment in Room 204",
    "assigned_to": 2,
    "attachment": "base64_encoded_file_or_url"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "category": "Academic",
        "priority": "High",
        "message": "Issue with lab equipment in Room 204",
        "status": "Pending",
        "created_at": "2024-01-15T10:30:00Z",
        "student_id": 1
    }
}
```

#### **Update Complaint**
```http
PUT /complaints/{id}
```

**Request Body:**
```json
{
    "status": "In-Progress",
    "comments": "Investigating the issue",
    "assigned_to": 3
}
```

#### **Forward Complaint**
```http
POST /complaints/{id}/forward
```

**Request Body:**
```json
{
    "to_role": "teacher",
    "to_user_id": 5,
    "reason": "Requires technical expertise"
}
```

#### **Get Complaint History**
```http
GET /complaints/{id}/history
```

---

### 👥 Users

#### **Get Current User**
```http
GET /users/me
```

#### **Get Users by Role**
```http
GET /users?role=teacher&department=Computer Science
```

#### **Update User Profile**
```http
PUT /users/me
```

#### **Change Password**
```http
POST /users/me/password
```

---

### 📊 Analytics

#### **Get Dashboard Stats**
```http
GET /analytics/dashboard
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_complaints": 150,
        "pending_complaints": 25,
        "resolved_complaints": 120,
        "avg_response_time": 24.5,
        "satisfaction_rate": 4.8,
        "complaints_by_category": {
            "Academic": 80,
            "Administrative": 40,
            "Technical": 30
        },
        "complaints_by_status": {
            "Pending": 25,
            "In-Progress": 15,
            "Resolved": 120
        }
    }
}
```

#### **Get Complaint Trends**
```http
GET /analytics/trends?period=30d&group_by=day
```

#### **Get Performance Metrics**
```http
GET /analytics/performance?user_id=1&period=7d
```

---

### 🔔 Notifications

#### **Get Notifications**
```http
GET /notifications?unread_only=true
```

#### **Mark Notification as Read**
```http
PUT /notifications/{id}/read
```

#### **Mark All as Read**
```http
PUT /notifications/read-all
```

---

### 📁 Files

#### **Upload File**
```http
POST /files/upload
Content-Type: multipart/form-data

file: [binary data]
type: complaint_attachment
```

#### **Get File**
```http
GET /files/{id}
```

#### **Delete File**
```http
DELETE /files/{id}
```

---

### 🏢 Departments

#### **Get Departments**
```http
GET /departments
```

#### **Get Department Users**
```http
GET /departments/{id}/users
```

---

### 🛡️ Security

#### **Get Security Events**
```http
GET /security/events?user_id=1&event_type=login_failed
```

#### **Enable 2FA**
```http
POST /security/2fa/enable
```

#### **Verify 2FA**
```http
POST /security/2fa/verify
```

---

## ❌ Error Handling

### Error Response Format
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Invalid input data",
        "details": {
            "field": "email",
            "reason": "Invalid email format"
        }
    }
}
```

### HTTP Status Codes
| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 409 | Conflict |
| 422 | Validation Error |
| 429 | Rate Limited |
| 500 | Internal Server Error |

### Error Codes
| Code | Description |
|------|-------------|
| `VALIDATION_ERROR` | Input validation failed |
| `AUTHENTICATION_FAILED` | Invalid credentials |
| `AUTHORIZATION_FAILED` | Insufficient permissions |
| `RESOURCE_NOT_FOUND` | Resource does not exist |
| `RATE_LIMIT_EXCEEDED` | Too many requests |
| `INTERNAL_ERROR` | Server error |

---

## ⏱️ Rate Limiting

### Limits
- **Authentication**: 5 requests per 15 minutes
- **General API**: 100 requests per minute
- **File Upload**: 10 requests per minute
- **Password Reset**: 3 requests per hour

### Headers
```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1640995200
```

### Rate Limited Response
```json
{
    "success": false,
    "error": {
        "code": "RATE_LIMIT_EXCEEDED",
        "message": "Too many requests. Try again later.",
        "retry_after": 60
    }
}
```

---

## 💡 Examples

### **JavaScript/Node.js**
```javascript
const axios = require('axios');

class DFCMSAPI {
    constructor(baseURL, apiKey) {
        this.client = axios.create({
            baseURL,
            headers: {
                'Authorization': `Bearer ${apiKey}`,
                'Content-Type': 'application/json'
            }
        });
    }

    async login(email, password) {
        try {
            const response = await this.client.post('/auth/login', {
                email,
                password
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response.data.error.message);
        }
    }

    async getComplaints(filters = {}) {
        try {
            const response = await this.client.get('/complaints', {
                params: filters
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response.data.error.message);
        }
    }

    async createComplaint(complaintData) {
        try {
            const response = await this.client.post('/complaints', complaintData);
            return response.data;
        } catch (error) {
            throw new Error(error.response.data.error.message);
        }
    }
}

// Usage
const api = new DFCMSAPI('https://api.dfcms.university.edu/v1');

async function example() {
    // Login
    const auth = await api.login('user@example.com', 'password');
    api.client.defaults.headers['Authorization'] = `Bearer ${auth.data.access_token}`;
    
    // Get complaints
    const complaints = await api.getComplaints({ status: 'Pending' });
    console.log(complaints.data.complaints);
    
    // Create complaint
    const newComplaint = await api.createComplaint({
        category: 'Academic',
        priority: 'High',
        message: 'Issue with lab equipment'
    });
    console.log(newComplaint.data);
}
```

### **Python**
```python
import requests
import json

class DFCMSAPI:
    def __init__(self, base_url):
        self.base_url = base_url
        self.token = None
        self.headers = {'Content-Type': 'application/json'}
    
    def login(self, email, password):
        response = requests.post(
            f'{self.base_url}/auth/login',
            json={'email': email, 'password': password},
            headers=self.headers
        )
        
        if response.status_code == 200:
            data = response.json()
            self.token = data['data']['access_token']
            self.headers['Authorization'] = f'Bearer {self.token}'
            return data
        else:
            raise Exception(response.json()['error']['message'])
    
    def get_complaints(self, filters=None):
        params = filters or {}
        response = requests.get(
            f'{self.base_url}/complaints',
            params=params,
            headers=self.headers
        )
        
        if response.status_code == 200:
            return response.json()
        else:
            raise Exception(response.json()['error']['message'])
    
    def create_complaint(self, complaint_data):
        response = requests.post(
            f'{self.base_url}/complaints',
            json=complaint_data,
            headers=self.headers
        )
        
        if response.status_code == 201:
            return response.json()
        else:
            raise Exception(response.json()['error']['message'])

# Usage
api = DFCMSAPI('https://api.dfcms.university.edu/v1')

# Login
auth = api.login('user@example.com', 'password')

# Get complaints
complaints = api.get_complaints({'status': 'Pending'})
print(complaints['data']['complaints'])

# Create complaint
new_complaint = api.create_complaint({
    'category': 'Academic',
    'priority': 'High',
    'message': 'Issue with lab equipment'
})
print(new_complaint['data'])
```

### **cURL**
```bash
# Login
curl -X POST https://api.dfcms.university.edu/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'

# Get complaints (with token)
curl -X GET https://api.dfcms.university.edu/v1/complaints \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"

# Create complaint
curl -X POST https://api.dfcms.university.edu/v1/complaints \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "category": "Academic",
    "priority": "High",
    "message": "Issue with lab equipment"
  }'
```

---

## 📝 Changelog

### v1.0.0 (2024-01-15)
- Initial API release
- Authentication endpoints
- Complaint management
- User management
- Basic analytics

### v1.1.0 (2024-02-01)
- File upload endpoints
- Advanced analytics
- Security event logging
- Rate limiting

### v1.2.0 (2024-03-01)
- Two-factor authentication
- Department management
- Enhanced filtering
- Performance improvements

---

## 📞 Support

- **Documentation**: https://docs.dfcms.university.edu
- **API Status**: https://status.dfcms.university.edu
- **Support**: api-support@dfcms.university.edu
- **GitHub**: https://github.com/Kenenisaboru/dfcms/issues

---

<div align="center">

**🚀 DFCMS API - Powering Digital Transformation**

*Version 1.2.0 | Last Updated: March 2024*

</div>
