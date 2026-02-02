# GitHub Setup and Deployment Guide

This guide walks you through setting up a GitHub repository and uploading the Event Registration module with proper commit history.

---

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Initial Repository Setup](#initial-repository-setup)
3. [Commit Strategy](#commit-strategy)
4. [Pushing to GitHub](#pushing-to-github)
5. [Repository Structure](#repository-structure)
6. [For Reviewers](#for-reviewers)
7. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Required Tools
- Git installed on your system
- GitHub account
- Command line access

### Verify Git Installation
```bash
git --version
# Should output: git version 2.x.x
```

### Configure Git (if not already done)
```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

---

## Initial Repository Setup

### Step 1: Create GitHub Repository

1. Go to [GitHub](https://github.com)
2. Click the **"+"** icon in top-right corner
3. Select **"New repository"**
4. Fill in details:
   - **Repository name:** `drupal-event-registration`
   - **Description:** "Custom Drupal 10 module for event registration with email notifications"
   - **Visibility:** Public (or Private, your choice)
   - **DO NOT** initialize with README (we have our own)
   - **DO NOT** add .gitignore (we have our own)
   - **License:** GPL-2.0 (recommended for Drupal modules)
5. Click **"Create repository"**

### Step 2: Note Your Repository URL
GitHub will show you the repository URL, like:
```
https://github.com/yourusername/drupal-event-registration.git
```
or
```
git@github.com:yourusername/drupal-event-registration.git
```

---

## Commit Strategy

We'll create meaningful commits at each development stage to show proper version control practices.

### Step 3: Initialize Local Repository

Navigate to your module directory:
```bash
cd /path/to/event_registration_module
```

Initialize Git:
```bash
git init
```

### Step 4: Create Commits with Proper History

#### Commit 1: Initial Module Structure
```bash
git add event_registration/event_registration.info.yml
git add event_registration/event_registration.permissions.yml
git add event_registration/event_registration.routing.yml
git add event_registration/event_registration.services.yml
git add event_registration/event_registration.module
git add event_registration/event_registration.install
git add event_registration/event_registration.libraries.yml
git add composer.json
git add composer.lock
git add .gitignore

git commit -m "Initial module structure with info, permissions, and routing files

- Added module info file with Drupal 10 compatibility
- Defined three permissions: administer, view registrations, register for events
- Set up routing for all forms and admin pages
- Created services definition for dependency injection
- Added hook_schema for database tables
- Configured libraries for JavaScript
- Added composer.json for project dependencies"
```

#### Commit 2: Database Service Layer
```bash
git add event_registration/src/Service/EventRegistrationDatabaseService.php

git commit -m "Implemented database service with dependency injection

- Created EventRegistrationDatabaseService class
- All database operations isolated in service layer
- Methods for CRUD operations on events and registrations
- Duplicate check functionality
- Query filtering for admin listing
- Follows PSR-4 autoloading standards
- No hard-coded Drupal static calls"
```

#### Commit 3: Validation Service
```bash
git add event_registration/src/Service/EventRegistrationValidationService.php

git commit -m "Added validation service for form inputs

- Email format validation using filter_var
- Special character validation for text fields
- Regex pattern: /^[a-zA-Z0-9\s.\-]+$/
- Duplicate registration check
- User-friendly error messages
- Integrated with database service via dependency injection"
```

#### Commit 4: Email Service
```bash
git add event_registration/src/Service/EventRegistrationEmailService.php

git commit -m "Implemented email notification service

- Uses Drupal Mail API properly
- Sends confirmation to users
- Conditional admin notifications
- Email content includes all registration details
- Config API integration for admin settings
- No hard-coded email addresses"
```

#### Commit 5: Configuration Forms
```bash
git add event_registration/src/Form/EventRegistrationConfigForm.php
git add event_registration/src/Form/EventConfigForm.php

git commit -m "Created admin configuration forms

- EventRegistrationConfigForm for module settings
- EventConfigForm for event creation
- Uses Config API for settings storage
- Date validation logic
- Proper form API implementation
- Follows Drupal form standards"
```

#### Commit 6: Registration Form with AJAX
```bash
git add event_registration/src/Form/EventRegistrationForm.php

git commit -m "Built event registration form with AJAX functionality

- Dynamic dropdowns using AJAX callbacks
- Category selection triggers date loading
- Date selection triggers event name loading
- Form validation integrated with validation service
- Database save via database service
- Email trigger via email service
- Proper dependency injection throughout"
```

#### Commit 7: AJAX Controllers
```bash
git add event_registration/src/Controller/AjaxController.php

git commit -m "Added AJAX controller for dynamic data loading

- Endpoints for event dates by category
- Endpoints for event names by category and date
- Admin listing AJAX endpoints
- JSON response formatting
- Request parameter validation
- Dependency injection for database service"
```

#### Commit 8: Admin Listing Controller
```bash
git add event_registration/src/Controller/RegistrationListingController.php

git commit -m "Implemented admin listing and CSV export

- Registration listing with filters
- Dynamic table population via AJAX
- CSV export functionality with proper formatting
- Participant count calculation
- Proper file headers for CSV download
- Permission-based access control"
```

#### Commit 9: JavaScript for Admin Interface
```bash
git add event_registration/js/admin_listing.js

git commit -m "Added JavaScript for admin listing interactions

- Event date filter change handler
- Event name filter change handler
- AJAX calls to backend endpoints
- Dynamic table rendering
- Participant count display
- CSV export button functionality
- jQuery integration with Drupal behaviors"
```

#### Commit 10: Database Schema SQL
```bash
git add event_registration.sql

git commit -m "Added SQL dump for database tables

- event_configuration table schema
- event_registration table schema
- Indexes for performance
- Unique constraint for duplicate prevention
- Foreign key relationship
- Sample data for testing (commented)"
```

#### Commit 11: Documentation
```bash
git add README.md
git add TESTING.md
git add GITHUB_SETUP.md

git commit -m "Added comprehensive documentation

- Complete README with installation steps
- URLs and paths documentation
- Database structure explanation
- Validation and email logic details
- Testing guide with all test cases
- GitHub setup and deployment guide
- Troubleshooting section"
```

### Step 5: Verify Commit History
```bash
git log --oneline
```

You should see 11 commits with descriptive messages.

---

## Pushing to GitHub

### Step 6: Connect to Remote Repository

```bash
# Add remote (replace with your actual URL)
git remote add origin https://github.com/yourusername/drupal-event-registration.git

# Verify remote
git remote -v
```

### Step 7: Push All Commits

```bash
# Push to main branch
git push -u origin main
```

If your default branch is `master` instead of `main`:
```bash
git branch -M main
git push -u origin main
```

### Step 8: Verify Upload

1. Go to your GitHub repository
2. Verify all files are present
3. Check commit history: Click on "X commits" link
4. Verify all 11 commits are visible with proper messages

---

## Repository Structure

Your GitHub repository should look like this:

```
drupal-event-registration/
├── .gitignore
├── README.md
├── TESTING.md
├── GITHUB_SETUP.md
├── composer.json
├── composer.lock
├── event_registration.sql
└── event_registration/
    ├── event_registration.info.yml
    ├── event_registration.permissions.yml
    ├── event_registration.routing.yml
    ├── event_registration.services.yml
    ├── event_registration.module
    ├── event_registration.install
    ├── event_registration.libraries.yml
    ├── js/
    │   └── admin_listing.js
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

---

## For Reviewers

### Quick Start Instructions

Add this section to your GitHub README or create INSTALL_FOR_REVIEWERS.md:

```markdown
## For Reviewers - Quick Installation

### 1. Clone Repository
```bash
git clone https://github.com/yourusername/drupal-event-registration.git
cd drupal-event-registration
```

### 2. Copy Module to Drupal
```bash
cp -r event_registration /path/to/drupal/web/modules/custom/
```

### 3. Import Database
```bash
# Option A: Using Drush (after enabling module)
cd /path/to/drupal
drush en event_registration -y
drush cr

# Option B: Manual SQL import
mysql -u username -p database_name < event_registration.sql
```

### 4. Configure Permissions
Navigate to: `/admin/people/permissions`
- Assign "register for events" to Authenticated users
- Assign "view event registrations" to Content editors
- Assign all permissions to Administrators

### 5. Configure Module Settings
Navigate to: `/admin/config/event-registration/settings`
- Set admin notification email
- Enable admin notifications

### 6. Create Test Event
Navigate to: `/admin/config/event-registration/events`
- Create a test event with current dates

### 7. Test Registration
Navigate to: `/event-registration`
- Complete registration form

### 8. View Registrations
Navigate to: `/admin/event-registration/registrations`
- Filter and view registrations
- Export as CSV

## Testing
See [TESTING.md](TESTING.md) for comprehensive testing procedures.

## Support
For issues, please check:
1. [README.md](README.md) - Complete documentation
2. [TESTING.md](TESTING.md) - Testing procedures
3. Drupal logs: `/admin/reports/dblog`
```

---

## Making Updates

### After Making Changes

```bash
# Check what changed
git status

# Stage changes
git add path/to/changed/file

# Commit with descriptive message
git commit -m "Fixed validation bug in email field

- Updated regex pattern to allow plus signs
- Added test case for plus sign emails
- Updated documentation"

# Push to GitHub
git push origin main
```

### Creating Feature Branches

For significant features:
```bash
# Create new branch
git checkout -b feature/new-functionality

# Make changes and commit
git add .
git commit -m "Description of changes"

# Push branch
git push origin feature/new-functionality

# Create Pull Request on GitHub
```

---

## Best Practices

### Commit Messages

✅ **Good:**
```
Added CSV export functionality

- Implemented export controller method
- Added proper CSV headers
- Formatted dates correctly
- Added download button to admin listing
```

❌ **Bad:**
```
updates
```

### Commit Frequency

- Commit after completing each logical unit of work
- Don't commit broken code
- Don't commit all changes at once at the end

### What to Commit

✅ **Do commit:**
- Source code
- Documentation
- Configuration files
- SQL schema files

❌ **Don't commit:**
- `vendor/` directory
- IDE configuration files
- Temporary files
- Sensitive data (API keys, passwords)

---

## Troubleshooting

### Issue: Git push rejected
```bash
# Pull latest changes first
git pull origin main

# Resolve conflicts if any
# Then push again
git push origin main
```

### Issue: Wrong commit message
```bash
# Amend last commit message
git commit --amend -m "Corrected message"

# Force push (if already pushed)
git push origin main --force
```

### Issue: Need to undo last commit
```bash
# Keep changes, undo commit
git reset --soft HEAD~1

# Discard changes, undo commit
git reset --hard HEAD~1
```

### Issue: Large file warning
GitHub has 100MB file limit. If you hit this:
```bash
# Remove large file from git
git rm --cached path/to/large/file

# Add to .gitignore
echo "path/to/large/file" >> .gitignore

# Commit
git commit -m "Removed large file"
```

---

## Repository Maintenance

### Adding Tags/Releases

After completing the module:
```bash
# Create tag
git tag -a v1.0.0 -m "Version 1.0.0 - Initial release"

# Push tag
git push origin v1.0.0
```

On GitHub:
1. Go to "Releases"
2. Click "Draft a new release"
3. Select tag v1.0.0
4. Add release notes
5. Publish release

### README Badges (Optional)

Add to your README.md:
```markdown
![Drupal](https://img.shields.io/badge/Drupal-10-blue)
![PHP](https://img.shields.io/badge/PHP-8.1+-purple)
![License](https://img.shields.io/badge/License-GPL--2.0-green)
```

---

## Submission

### For Assignment Submission

1. Verify repository is public (or give reviewer access)
2. Ensure all commits are pushed
3. Test cloning from a fresh location
4. Submit repository URL in assignment form
5. Include these files at root:
   - ✅ README.md
   - ✅ TESTING.md  
   - ✅ composer.json
   - ✅ composer.lock
   - ✅ event_registration.sql
   - ✅ event_registration/ (module directory)

---

## Additional Resources

- [Git Documentation](https://git-scm.com/doc)
- [GitHub Guides](https://guides.github.com/)
- [Drupal Git Documentation](https://www.drupal.org/documentation/git)
- [Conventional Commits](https://www.conventionalcommits.org/)

---

**Last Updated:** January 28, 2026
