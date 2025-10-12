# LMS – User Manual

Welcome to your Library Management System. This guide helps librarians, admins, and students use the site at https://aoishik.tech.

## Overview
- Role-based access: Admin, Librarian, Student
- Core features: Books inventory, Students directory, Issue/Return with fines, Reports & exports
- Modern UI: Light/Dark theme, mobile-friendly with a hamburger menu

## Getting Started
1. Open the site: https://aoishik.tech
2. Create an account (Sign up) or log in if you already have one.
3. Your dashboard shows quick stats and recent activity.

## Roles and Permissions
- Admin: Full access. Manage librarians, students, books, issue/return, reports, and settings.
- Librarian: Manage students, books, issue/return, reports.
- Student: View books, reserve (if enabled), view personal history.

## Top Navigation
- Dashboard: Overview of stats and monthly trends.
- Books: View, add, edit, delete books (admins/librarians). Students can browse/search.
- Students: Manage student records (admins/librarians).
- Issue: Issue and return books; fines are calculated automatically.
- Reports: Analytics plus CSV/PDF exports (inventory, issued, overdue).
- Librarians: Admin-only management of librarian accounts.
- Settings: Personal profile and app settings (admin controls include fines, due days, theme, reservations).

On mobile, use the hamburger icon to open the menu. Tap outside the menu to close it.

## Books
- Add a new book with title, author, ISBN, category, year, publisher, and quantity.
- Search and filter by title or author.
- Reservations are optional (controlled by admin in Settings).

## Students
- Add or edit student records (name, email, student ID, department, etc.).
- Students created via Sign up are automatically linked to a user account.

## Issue / Return
- Issue: Select a student and a book. Due date is pre-filled based on settings.
- Return: Scan/search the issue record and complete return. Fines apply for late returns based on “Fine per day”.
- Safety: Return is idempotent—re-running a return won’t double-charge.

## Reports & Exports
- Analytics cards: Open issues, overdue, total students, total books.
- Charts: Monthly issuance trends.
- Exports:
  - Inventory CSV and PDF
  - Issued books CSV
  - Overdue CSV

## Settings
- App name, fine per day, default due days, default theme, reservation toggle.
- These values affect UI labels, issuing defaults, and fine calculations.

## Theme
- Click the sun/moon icon to switch light/dark mode.
- Your choice is remembered in the browser.

## Mobile Tips
- Tap the hamburger icon to open navigation.
- If your mobile browser shows an overlay banner, the site ensures the top bar stays clickable.

## FAQ
**I can’t see or click the menu on my phone.**
Try refreshing the page. The top bar is pinned above overlays and should always receive taps.

**Why can’t students add books?**
Only Admins/Librarians can add or edit books by design.

**How are fines calculated?**
Fines = (days past due) × (Fine per day) set in Settings.

## Troubleshooting
- If pages look odd, clear your browser cache and reload.
- If exports don’t download, ensure pop-ups/downloads are allowed.

## Privacy & Security
- Passwords are hashed for security.
- Admin-only utilities (like one-time password reset tools) are not deployed publicly.

## Self-Hosting Notes (for developers)
- Database and secrets must not be committed to Git.
- Use the provided `config/db.local.php` (ignored by Git) to set credentials.

## Support
Have feedback or found an issue? Open an issue on the GitHub repository or reach out via the site footer link. Email me at: lms@aoishik.tech
# PHP + MySQL Library Management System (LMS)

Modern, minimal, and responsive Library Management System built with PHP, MySQL, Tailwind CSS, and vanilla JavaScript. Works on InfinityFree hosting.

## Features
- Role-based auth (Admin/Librarian, Student)
- Secure password hashing (password_hash)
- Dashboard metrics and charts (Chart.js)
- Books and Students management (CRUD)
- Issue/Return books with auto fine calculation
- CSV export (inventory)
- Dark/Light mode with preference persistence
- QR codes for books (QRCode.js)

## Tech
- PHP 7.4+ (works on InfinityFree)
- MySQL (MariaDB)
- Tailwind CDN, Chart.js, SweetAlert2, QRCode.js

## File Structure
- /assets (css, js, images)
- /config/db.php
- /includes (header, footer, navbar, auth)
- /pages (login, dashboard, books, students, issue, reports, settings)
- index.php
