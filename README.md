# Event Registration Module for Drupal 10

A custom Drupal 10 module that allows users to register for events via a custom form, stores registrations in custom database tables, and sends email notifications to both users and administrators.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Database Structure](#database-structure)
- [URLs and Paths](#urls-and-paths)
- [Validation Logic](#validation-logic)
- [Email Notifications](#email-notifications)
- [Testing](#testing)
- [Code Structure](#code-structure)
- [Troubleshooting](#troubleshooting)

---

## Features

### 1. Event Configuration
- Admins can create events with:
  - Event Name
  - Event Category (Online Workshop, Hackathon, Conference, One-day Workshop)
  - Event Date
  - Registration Start Date
  - Registration End Date

### 2. User Registration Form
- Dynamic form with AJAX-powered dropdowns
- Fields:
  - Full Name (text, required)
  - Email Address (email, required)
  - College Name (text, required)
  - Department (text, required)
  - Event Category (dropdown, required)
  - Event Date (dropdown, required - populated via AJAX)
  - Event Name (dropdown, required - populated via AJAX)

### 3. Data Validation
- Email format validation
- Special character validation for text fields
- Duplicate registration prevention (Email + Event Date)
- User-friendly error messages

### 4. Email Notifications
- Confirmation emails sent to registered users
- Admin notification emails (configurable)
- Email includes all registration details

### 5. Admin Management
- View all registrations
- Filter by Event Date and Event Name (with AJAX)
- Display total participant count
- Export registrations as CSV

### 6. Permissions System
- `administer event registration` - Configure settings and events
- `view event registrations` - View and export registration data
- `register for events` - Access registration form

---

## Requirements

- **Drupal**: 10.x
- **PHP**: 8.1 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Web Server**: Apache or Nginx

---

## Installation

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd event_registration_module
```

### Step 2: Copy Module to Drupal

Copy the `event_registration` folder to your Drupal installation:

```bash
cp -r event_registration /path/to/drupal/web/modules/custom/
```

If the `custom` folder doesn't exist, create it:

```bash
mkdir -p /path/to/drupal/web/modules/custom/
```

### Step 3: Import Database Tables

You have two options:

#### Option A: Using Drush (Recommended)
```bash
cd /path/to/drupal
drush en event_registration -y
```

#### Option B: Manual SQL Import
```bash
mysql -u [username] -p [database_name] < event_registration.sql
```

### Step 4: Enable the Module

Using Drush:
```bash
drush en event_registration -y
drush cr
```

Or via Drupal UI:
1. Navigate to `Admin` > `Extend` (`/admin/modules`)
2. Find "Event Registration" under "Custom"
3. Check the box and click "Install"
4. Clear cache: `drush cr` or via UI

### Step 5: Set Permissions

Navigate to `Admin` > `People` > `Permissions` (`/admin/people/permissions`)

Assign appropriate permissions to user roles:
- **Authenticated users**: `register for events`
- **Content editors**: `view event registrations`
- **Administrators**: All permissions

---

## Configuration

### 1. Configure Email Settings

Navigate to: `/admin/config/event-registration/settings`

- Set **Admin Notification Email**: Email address for admin notifications
- Enable/Disable **Admin Notifications**: Toggle admin email notifications

### 2. Create Events

Navigate to: `/admin/config/event-registration/events`

Fill in the event configuration form:
- Event Name
- Category
- Event Date
- Registration Start Date
- Registration End Date

**Note**: Registration form will only show events where current date falls between registration start and end dates.

---

## Usage

### For Users - Registering for Events

1. Navigate to: `/event-registration`
2. Fill in personal information
3. Select Event Category
4. Select Event Date (auto-populated based on category)
5. Select Event Name (auto-populated based on category and date)
6. Click "Register"
7. Receive confirmation email

### For Admins - Managing Registrations

1. Navigate to: `/admin/event-registration/registrations`
2. Select Event Date from dropdown
3. Select Event Name (auto-populated)
4. View registrations table with:
   - Name
   - Email
   - Event Date
   - College Name
   - Department
   - Submission Date
5. See total participant count
6. Click "Export as CSV" to download data

---

## Database Structure

### Table: `event_configuration`

Stores event configuration details.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (Primary Key) | Auto-increment event ID |
| event_name | VARCHAR(255) | Name of the event |
| event_category | VARCHAR(100) | Category (Online Workshop, Hackathon, etc.) |
| event_date | VARCHAR(20) | Date when event occurs |
| registration_start_date | VARCHAR(20) | Registration opening date |
| registration_end_date | VARCHAR(20) | Registration closing date |
| created | INT | Unix timestamp of creation |

**Indexes:**
- Primary Key: `id`
- Index: `event_category`
- Index: `event_date`

### Table: `event_registration`

Stores user registration data.

| Column | Type | Description |
|--------|------|-------------|
| id | INT (Primary Key) | Auto-increment registration ID |
| full_name | VARCHAR(255) | Registrant's full name |
| email | VARCHAR(255) | Registrant's email |
| college_name | VARCHAR(255) | College name |
| department | VARCHAR(255) | Department name |
| event_category | VARCHAR(100) | Selected event category |
| event_date | VARCHAR(20) | Selected event date |
| event_id | INT (Foreign Key) | References event_configuration.id |
| created | INT | Unix timestamp of registration |

**Indexes:**
- Primary Key: `id`
- Unique Key: `email_event_date` (prevents duplicates)
- Index: `email`
- Index: `event_id`
- Index: `event_date`

**Foreign Key:**
- `event_id` references `event_configuration(id)`

---

## URLs and Paths

| Description | URL Path | Permission Required |
|-------------|----------|---------------------|
| Registration Form | `/event-registration` | `register for events` |
| Event Configuration | `/admin/config/event-registration/events` | `administer event registration` |
| Module Settings | `/admin/config/event-registration/settings` | `administer event registration` |
| Admin Listing | `/admin/event-registration/registrations` | `view event registrations` |
| CSV Export | `/admin/event-registration/export` | `view event registrations` |

**AJAX Endpoints:**
- `/event-registration/ajax/event-dates` - Get dates by category
- `/event-registration/ajax/event-names` - Get names by category and date
- `/admin/event-registration/ajax/event-names` - Get names by date (admin)
- `/admin/event-registration/ajax/registrations` - Get filtered registrations

---

## Validation Logic

### 1. Email Validation
- Uses PHP's `filter_var()` with `FILTER_VALIDATE_EMAIL`
- Ensures proper email format
- Error: "Please enter a valid email address."

### 2. Text Field Validation
Applied to: Full Name, College Name, Department

- **Allowed**: Letters (a-z, A-Z), Numbers (0-9), Spaces, Dots (.), Hyphens (-)
- **Not Allowed**: Special characters like @, #, $, %, &, *, etc.
- **Regex**: `/^[a-zA-Z0-9\s.\-]+$/`
- Error: "The [Field Name] field contains invalid characters. Only letters, numbers, spaces, dots, and hyphens are allowed."

### 3. Duplicate Registration Check
- Checks combination of Email + Event Date
- Queries database using unique key constraint
- Error: "You have already registered for an event on this date. Duplicate registrations are not allowed."

### 4. Date Validation
For event configuration:
- Registration End Date must be after Start Date
- Event Date must be after Registration End Date
- Prevents illogical date configurations

### Implementation
All validation is handled through:
- **Service**: `EventRegistrationValidationService`
- **Dependency Injection**: Injected into form classes
- **No hard-coded \Drupal calls** in business logic

---

## Email Notifications

### Email Flow

1. **User Registration Submitted** → Validation → Database Save → Email Trigger

2. **User Email** (always sent):
   - To: Registrant's email address
   - Subject: "Event Registration Confirmation - [Event Name]"
   - Content: All registration details

3. **Admin Email** (conditional):
   - To: Admin email (from configuration)
   - Enabled: Only if "Enable Admin Notifications" is checked
   - Subject: "New Event Registration - [Event Name]"
   - Content: Same as user email

### Email Content

```
Dear [Full Name],

Thank you for registering for the event. Below are your registration details:

Name: [Full Name]
Event Name: [Event Name]
Event Date: [Event Date]
Category: [Event Category]
College: [College Name]
Department: [Department]

We look forward to seeing you at the event!

Best regards,
Event Management Team
```

### Implementation
- Uses Drupal Mail API
- Service: `EventRegistrationEmailService`
- Hook: `event_registration_mail()` in `.module` file
- No hard-coded email addresses (uses Config API)

---

## Testing

### 1. Manual Testing Steps

#### Test Event Configuration
1. Navigate to `/admin/config/event-registration/events`
2. Create an event with:
   - Name: "Test Workshop"
   - Category: "Online Workshop"
   - Event Date: Future date (e.g., 2026-03-15)
   - Registration Start: Today's date
   - Registration End: Date before event date
3. Verify success message

#### Test User Registration
1. Navigate to `/event-registration`
2. Fill form with valid data:
   - Full Name: "John Doe"
   - Email: "john@example.com"
   - College: "Test University"
   - Department: "Computer Science"
   - Select category, date, and event
3. Submit and verify:
   - Success message appears
   - Email received
   - Database entry created

#### Test Validation
1. Try registering with:
   - Invalid email: "notanemail"
   - Special characters in name: "John@Doe#"
   - Same email + date combination (duplicate)
2. Verify appropriate error messages

#### Test AJAX Functionality
1. On registration form:
   - Select category → Verify dates populate
   - Select date → Verify event names populate
2. On admin listing:
   - Select date → Verify event names populate
   - Select event → Verify table updates

#### Test Admin Listing
1. Navigate to `/admin/event-registration/registrations`
2. Filter by date and event
3. Verify:
   - Table displays correctly
   - Participant count is accurate
   - CSV export works

### 2. Database Verification

```sql
-- Check event configuration
SELECT * FROM event_configuration;

-- Check registrations
SELECT * FROM event_registration;

-- Check for duplicate prevention
SELECT email, event_date, COUNT(*) as count 
FROM event_registration 
GROUP BY email, event_date 
HAVING count > 1;
```

### 3. Email Testing

Configure test email in Drupal:
```bash
drush config:set system.mail interface.default test_mail_collector -y
```

Or install and enable Maillog module to capture emails.

---

## Code Structure

### PSR-4 Autoloading
All classes follow PSR-4 standard:
```
event_registration/
└── src/
    ├── Controller/
    │   ├── AjaxController.php
    │   └── RegistrationListingController.php
    ├── Form/
    │   ├── EventConfigForm.php
    │   ├── EventRegistrationConfigForm.php
    │   └── EventRegistrationForm.php
    └── Service/
        ├── EventRegistrationDatabaseService.php
        ├── EventRegistrationEmailService.php
        └── EventRegistrationValidationService.php
```

### Dependency Injection
All services use constructor injection:
```php
public function __construct(
  EventRegistrationDatabaseService $database_service,
  EventRegistrationEmailService $email_service
) {
  $this->databaseService = $database_service;
  $this->emailService = $email_service;
}
```

**No `\Drupal::service()` calls in business logic!**

### Services
Defined in `event_registration.services.yml`:
- `event_registration.database_service`
- `event_registration.email_service`
- `event_registration.validation_service`

### Configuration
Uses Config API for settings:
- Config name: `event_registration.settings`
- Keys: `admin_notification_email`, `enable_admin_notifications`

---

## Troubleshooting

### Issue: Module won't enable
**Solution:**
```bash
drush cr
drush en event_registration -y
```

### Issue: Database tables not created
**Solution:**
```bash
# Reinstall module
drush pmu event_registration -y
drush en event_registration -y

# Or manually import SQL
mysql -u [user] -p [database] < event_registration.sql
```

### Issue: Permissions denied
**Solution:**
1. Go to `/admin/people/permissions`
2. Assign appropriate permissions to roles
3. Clear cache

### Issue: AJAX not working
**Solution:**
1. Clear Drupal cache: `drush cr`
2. Check browser console for JavaScript errors
3. Verify jQuery is loaded
4. Check that library is attached in controller

### Issue: Emails not sending
**Solution:**
1. Check email configuration: `/admin/config/system/site-information`
2. Verify SMTP settings (if using SMTP module)
3. Check admin settings: `/admin/config/event-registration/settings`
4. Install Maillog module to debug email issues

### Issue: CSV export is empty
**Solution:**
1. Ensure event date and event name are selected
2. Verify registrations exist in database
3. Check browser downloads folder
4. Clear cache and try again

---

## Development Notes

### Coding Standards
- Follows Drupal coding standards
- Uses PHPDoc comments
- No hard-coded values
- Dependency injection throughout

### Git Commit Strategy
Commit frequently with meaningful messages:
```bash
git add .
git commit -m "Added event configuration form"
git add .
git commit -m "Implemented email notifications"
git add .
git commit -m "Added CSV export functionality"
```

### Future Enhancements
- Add event capacity limits
- Implement waiting list functionality
- Add calendar view for events
- Multi-language support
- QR code generation for registrations

---

## Support

For issues or questions:
1. Check this README thoroughly
2. Review Drupal logs: `/admin/reports/dblog`
3. Enable debugging: `drush en devel -y`
4. Check browser console for JavaScript errors

---

## License

GPL-2.0-or-later

---

## Author

Developed as a custom Drupal 10 module following best practices and Drupal coding standards.

**Last Updated:** January 28, 2026
