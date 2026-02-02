# Testing Guide for Event Registration Module

This document provides comprehensive testing procedures for the Event Registration module.

---

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Initial Setup Testing](#initial-setup-testing)
3. [Event Configuration Testing](#event-configuration-testing)
4. [User Registration Testing](#user-registration-testing)
5. [Validation Testing](#validation-testing)
6. [Email Notification Testing](#email-notification-testing)
7. [Admin Listing Testing](#admin-listing-testing)
8. [AJAX Functionality Testing](#ajax-functionality-testing)
9. [CSV Export Testing](#csv-export-testing)
10. [Permission Testing](#permission-testing)
11. [Database Integrity Testing](#database-integrity-testing)

---

## Prerequisites

### Required Access
- Drupal site with administrator privileges
- Access to database (phpMyAdmin or command line)
- Access to email inbox or email testing tool

### Test User Accounts
Create these user accounts for permission testing:
1. Administrator (full permissions)
2. Event Manager (administer event registration, view event registrations)
3. Authenticated User (register for events only)
4. Anonymous User (no permissions)

---

## Initial Setup Testing

### Test 1.1: Module Installation
**Steps:**
1. Copy module to `/web/modules/custom/event_registration`
2. Enable module: `drush en event_registration -y`
3. Clear cache: `drush cr`

**Expected Results:**
- ✅ Module enables without errors
- ✅ Success message: "Module event_registration has been enabled"
- ✅ Database tables created

**Verification:**
```sql
SHOW TABLES LIKE 'event_%';
-- Should show: event_configuration, event_registration
```

### Test 1.2: Permissions Configuration
**Steps:**
1. Navigate to `/admin/people/permissions`
2. Verify permissions exist:
   - administer event registration
   - view event registrations
   - register for events

**Expected Results:**
- ✅ All three permissions are listed
- ✅ Permissions can be assigned to roles

---

## Event Configuration Testing

### Test 2.1: Access Event Configuration Page
**Steps:**
1. Login as administrator
2. Navigate to `/admin/config/event-registration/events`

**Expected Results:**
- ✅ Page loads successfully
- ✅ Form displays with all required fields
- ✅ Category dropdown shows 4 options

### Test 2.2: Create Valid Event
**Test Data:**
- Event Name: "Web Development Workshop"
- Category: "Online Workshop"
- Event Date: 2026-03-15
- Registration Start: 2026-01-20
- Registration End: 2026-03-10

**Steps:**
1. Fill form with test data
2. Click "Save Event"

**Expected Results:**
- ✅ Success message: "Event has been successfully configured"
- ✅ Form clears after submission

**Database Verification:**
```sql
SELECT * FROM event_configuration ORDER BY id DESC LIMIT 1;
-- Should show the newly created event
```

### Test 2.3: Date Validation
**Test Case A: End Date Before Start Date**
- Registration Start: 2026-03-10
- Registration End: 2026-03-01

**Expected Result:**
- ❌ Error: "Registration end date must be after the start date"

**Test Case B: Event Date Before Registration End**
- Event Date: 2026-03-01
- Registration End: 2026-03-10

**Expected Result:**
- ❌ Error: "Event date must be after the registration end date"

### Test 2.4: Create Multiple Events
**Test Data:**
Create 3 events:
1. Online Workshop - 2026-02-15
2. Hackathon - 2026-03-01  
3. Conference - 2026-03-20

**Expected Results:**
- ✅ All events save successfully
- ✅ Different categories work correctly

---

## User Registration Testing

### Test 3.1: Access Registration Form
**Steps:**
1. Login as authenticated user
2. Navigate to `/event-registration`

**Expected Results:**
- ✅ Form loads successfully
- ✅ All fields are visible
- ✅ Category dropdown populated with active events
- ✅ Date and Event Name dropdowns disabled initially

### Test 3.2: No Active Events
**Setup:**
1. Set all events' registration_end_date to past dates

**Steps:**
1. Navigate to `/event-registration`

**Expected Results:**
- ✅ Message: "There are no events available for registration at this time"
- ✅ No form fields displayed

### Test 3.3: Successful Registration
**Test Data:**
- Full Name: "John Doe"
- Email: "john.doe@test.com"
- College: "Test University"
- Department: "Computer Science"
- Category: Select "Online Workshop"
- Event Date: Select from populated dropdown
- Event Name: Select from populated dropdown

**Steps:**
1. Fill all fields
2. Click "Register"

**Expected Results:**
- ✅ Success message appears
- ✅ Confirmation email sent message
- ✅ Form clears

**Database Verification:**
```sql
SELECT * FROM event_registration WHERE email = 'john.doe@test.com';
-- Should show the new registration
```

---

## Validation Testing

### Test 4.1: Required Fields
**Steps:**
1. Leave each field empty one at a time
2. Try to submit

**Expected Results:**
- ❌ Error for each required field
- ❌ "Field is required" message
- ❌ Form not submitted

### Test 4.2: Email Validation
**Test Cases:**
| Input | Expected Result |
|-------|----------------|
| notanemail | ❌ Error: Invalid email |
| test@test | ❌ Error: Invalid email |
| test@test.com | ✅ Valid |
| john.doe+test@example.co.uk | ✅ Valid |

### Test 4.3: Special Characters in Text Fields
**Test Full Name Field:**
| Input | Expected Result |
|-------|----------------|
| John Doe | ✅ Valid |
| John-Doe | ✅ Valid |
| John.Doe | ✅ Valid |
| John@Doe | ❌ Error: Invalid characters |
| John#Doe | ❌ Error: Invalid characters |
| John$Doe | ❌ Error: Invalid characters |

**Repeat for College Name and Department fields**

### Test 4.4: Duplicate Registration Prevention
**Steps:**
1. Register with: john@test.com for event on 2026-03-15
2. Try registering again with same email and date

**Expected Results:**
- ❌ Error: "You have already registered for an event on this date"
- ❌ Form not submitted

**Test Allowed Duplicate:**
1. Register with: john@test.com for event on 2026-03-15
2. Register with: john@test.com for event on 2026-03-20

**Expected Results:**
- ✅ Both registrations successful
- ✅ Same email allowed for different dates

---

## Email Notification Testing

### Test 5.1: Configure Email Settings
**Steps:**
1. Navigate to `/admin/config/event-registration/settings`
2. Set Admin Email: "admin@test.com"
3. Enable admin notifications
4. Save configuration

**Expected Results:**
- ✅ Success message
- ✅ Settings saved

### Test 5.2: User Email
**Steps:**
1. Register for an event
2. Check email inbox

**Expected Results:**
- ✅ Email received at user's address
- ✅ Subject: "Event Registration Confirmation - [Event Name]"
- ✅ Body contains:
  - User's name
  - Event name
  - Event date
  - Category
  - College
  - Department

### Test 5.3: Admin Email
**Steps:**
1. Ensure admin notifications enabled
2. Register for an event
3. Check admin email inbox

**Expected Results:**
- ✅ Email received at admin address
- ✅ Subject: "New Event Registration - [Event Name]"
- ✅ Same content as user email

### Test 5.4: Admin Email Disabled
**Steps:**
1. Disable admin notifications in settings
2. Register for an event

**Expected Results:**
- ✅ User email still sent
- ✅ Admin email NOT sent

---

## Admin Listing Testing

### Test 6.1: Access Admin Listing
**Steps:**
1. Login as user with "view event registrations" permission
2. Navigate to `/admin/event-registration/registrations`

**Expected Results:**
- ✅ Page loads successfully
- ✅ Event Date dropdown populated
- ✅ Event Name dropdown disabled
- ✅ Export button hidden
- ✅ Empty table/participant count

### Test 6.2: Filter by Date
**Steps:**
1. Select a date with registrations
2. Observe changes

**Expected Results:**
- ✅ Event Name dropdown enables
- ✅ Event Name dropdown populated with events for that date
- ✅ Table remains empty
- ✅ Participant count remains empty

### Test 6.3: Filter by Event
**Steps:**
1. Select event date
2. Select event name

**Expected Results:**
- ✅ Table populates with registrations
- ✅ Participant count displays: "Total Participants: X"
- ✅ Export button appears
- ✅ Table columns:
  - Name
  - Email
  - Event Date
  - College Name
  - Department
  - Submission Date

### Test 6.4: Table Data Accuracy
**Verification:**
Compare table data with database:
```sql
SELECT full_name, email, event_date, college_name, department, created
FROM event_registration
WHERE event_date = '2026-03-15' AND event_id = 1;
```

**Expected Results:**
- ✅ All data matches
- ✅ Dates formatted correctly
- ✅ Timestamps converted to readable dates

---

## AJAX Functionality Testing

### Test 7.1: Registration Form AJAX
**Test Event Date Update:**
1. Select event category
2. Observe event date dropdown

**Expected Results:**
- ✅ Date dropdown enables immediately
- ✅ Populated with dates for selected category
- ✅ Only dates within registration period shown
- ✅ No page reload

**Test Event Name Update:**
1. Select event category
2. Select event date
3. Observe event name dropdown

**Expected Results:**
- ✅ Event name dropdown enables immediately
- ✅ Populated with events for category and date
- ✅ No page reload

### Test 7.2: Admin Listing AJAX
**Test Event Name Update:**
1. Select event date
2. Observe event name dropdown

**Expected Results:**
- ✅ Dropdown populates immediately
- ✅ Shows only events for selected date
- ✅ No page reload

**Test Registrations Update:**
1. Select event date
2. Select event name

**Expected Results:**
- ✅ Table updates immediately
- ✅ Participant count updates
- ✅ Export button appears
- ✅ No page reload

### Test 7.3: JavaScript Console
**Steps:**
1. Open browser developer tools
2. Navigate to Console tab
3. Perform AJAX actions

**Expected Results:**
- ✅ No JavaScript errors
- ✅ AJAX requests visible in Network tab
- ✅ Responses return correct data

---

## CSV Export Testing

### Test 8.1: Export with Filters
**Steps:**
1. Navigate to admin listing
2. Select event date and event name
3. Click "Export as CSV"

**Expected Results:**
- ✅ CSV file downloads
- ✅ Filename format: `event_registrations_YYYY-MM-DD_HH-MM-SS.csv`
- ✅ File contains data

### Test 8.2: CSV Content
**Steps:**
1. Open downloaded CSV in Excel/Sheets

**Expected Results:**
- ✅ Header row present:
  - ID, Full Name, Email, College Name, Department, Event Category, Event Date, Submission Date
- ✅ Data rows match table display
- ✅ All fields populated correctly
- ✅ Special characters handled properly

### Test 8.3: Export All Registrations
**Steps:**
1. Select only event date (no specific event)
2. Export CSV

**Expected Results:**
- ✅ CSV contains all registrations for that date
- ✅ Multiple events included if applicable

---

## Permission Testing

### Test 9.1: Anonymous User
**Steps:**
1. Logout
2. Try accessing:
   - `/event-registration`
   - `/admin/config/event-registration/events`
   - `/admin/event-registration/registrations`

**Expected Results:**
- ❌ Access denied or login redirect for all pages

### Test 9.2: Authenticated User (register for events only)
**Steps:**
1. Login as user with only "register for events" permission
2. Access pages

**Expected Results:**
- ✅ Can access `/event-registration`
- ❌ Cannot access admin pages

### Test 9.3: Event Manager
**Steps:**
1. Login as user with "view event registrations" permission
2. Access pages

**Expected Results:**
- ✅ Can access `/admin/event-registration/registrations`
- ✅ Can export CSV
- ❌ Cannot access configuration pages (if not admin)

### Test 9.4: Administrator
**Steps:**
1. Login as administrator
2. Access all pages

**Expected Results:**
- ✅ Full access to all pages
- ✅ Can configure events
- ✅ Can view registrations
- ✅ Can export data

---

## Database Integrity Testing

### Test 10.1: Foreign Key Relationship
**Steps:**
```sql
-- Try to insert registration with non-existent event_id
INSERT INTO event_registration 
(full_name, email, college_name, department, event_category, event_date, event_id, created)
VALUES 
('Test', 'test@test.com', 'Test College', 'CS', 'Conference', '2026-03-15', 9999, UNIX_TIMESTAMP());
```

**Expected Results:**
- ❌ Error or warning (depending on DB config)
- ✅ Data integrity maintained

### Test 10.2: Unique Constraint
**Steps:**
```sql
-- Try to insert duplicate email + event_date
INSERT INTO event_registration 
(full_name, email, college_name, department, event_category, event_date, event_id, created)
VALUES 
('Duplicate', 'existing@test.com', 'College', 'Dept', 'Workshop', '2026-03-15', 1, UNIX_TIMESTAMP());

-- Run same query again
```

**Expected Results:**
- ✅ First insert succeeds
- ❌ Second insert fails: "Duplicate entry"

### Test 10.3: Data Consistency
**Steps:**
```sql
-- Check for orphaned registrations
SELECT r.* 
FROM event_registration r
LEFT JOIN event_configuration e ON r.event_id = e.id
WHERE e.id IS NULL;
```

**Expected Results:**
- ✅ No orphaned records
- ✅ All registrations link to valid events

---

## Performance Testing

### Test 11.1: Large Dataset
**Setup:**
Insert 1000+ registrations
```sql
-- Use a script or tool to insert bulk data
```

**Steps:**
1. Access admin listing
2. Filter by date with many registrations
3. Export CSV

**Expected Results:**
- ✅ Page loads in reasonable time (<3 seconds)
- ✅ AJAX responses fast (<1 second)
- ✅ CSV export completes successfully

### Test 11.2: Concurrent Registrations
**Steps:**
1. Open registration form in multiple browser tabs
2. Submit simultaneously with same email and date

**Expected Results:**
- ✅ Only one registration succeeds
- ❌ Others show duplicate error
- ✅ Database maintains integrity

---

## Test Checklist Summary

Use this checklist when testing:

- [ ] Module installation successful
- [ ] Permissions configured correctly
- [ ] Event configuration saves properly
- [ ] Date validation works
- [ ] Registration form accessible
- [ ] All validation rules enforced
- [ ] Duplicate prevention works
- [ ] User email sent correctly
- [ ] Admin email sent when enabled
- [ ] Admin listing displays data
- [ ] AJAX updates work smoothly
- [ ] CSV export contains correct data
- [ ] Permissions enforced properly
- [ ] Database integrity maintained
- [ ] No JavaScript console errors
- [ ] Performance acceptable

---

## Reporting Issues

When reporting bugs, include:
1. Steps to reproduce
2. Expected behavior
3. Actual behavior
4. Browser and version
5. Drupal version
6. PHP version
7. Error messages (from logs and console)
8. Screenshots if applicable

Check Drupal logs: `/admin/reports/dblog`

---

**Testing completed on:** [Date]  
**Tested by:** [Name]  
**Drupal version:** 10.x  
**Module version:** 1.0
