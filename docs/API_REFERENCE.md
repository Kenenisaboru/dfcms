# 🔌 SFCMS API Reference
## Internal RESTful Endpoints for Real-Time UI Synchronization

The SFCMS uses a series of PHP-based API endpoints to handle asynchronous requests (AJAX). All responses are returned in JSON format.

---

## 🔐 Authentication & Security
- **Required**: Most endpoints require a valid session.
- **CSRF**: POST requests must include a valid CSRF token in the headers or body.

---

## 📂 Complaint Management

### `GET /api/get_complaint_history.php`
Retrieves the full history of complaints for the logged-in student.
- **Query Params**: `limit` (int, optional)
- **Response**: 
  ```json
  [
    { "id": 1, "title": "...", "status": "Pending", "created_at": "..." },
    ...
  ]
  ```

---

## 🔔 Notifications System

### `GET /api/get_unread_count.php`
Returns the count of unread notifications for the current user.
- **Response**: `{ "count": 5 }`

### `GET /api/get_latest_notifications.php`
Fetches the 10 most recent notifications.
- **Response**: `{ "notifications": [...] }`

### `POST /api/mark_notification_read.php`
Marks a specific notification as read.
- **Body**: `{ "notification_id": 123 }`

### `POST /api/mark_all_notifications_read.php`
Marks all notifications for the user as read.

---

## 💬 Communication

### `GET/POST /api/chat_messages.php`
Retrieves or sends chat messages related to a specific complaint ticket.
- **GET Params**: `ticket_id`
- **POST Body**: `{ "ticket_id": 123, "message": "..." }`

### `POST /api/hod_broadcast.php`
Allows HODs to send a broadcast message to all students or departments.
- **Body**: `{ "title": "...", "message": "...", "target": "all" }`

---

## 🛠️ Integration Example (JavaScript)

```javascript
fetch('/api/get_unread_count.php')
  .then(response => response.json())
  .then(data => {
    document.getElementById('notif-badge').innerText = data.count;
  });
```

---
*DFCMS Engineering Core - API Version 1.0.0*
