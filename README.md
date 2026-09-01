# 🏠 Roommate Mobile Theme

**A custom WordPress theme powering [bkkroomie.com](https://bkkroomie.com/) — a platform for finding rooms and roommates in Bangkok.**

Built as a mobile-first WordPress theme from scratch (custom templates, no page builder), handling listings, user dashboards, messaging, and profile management for a real, live production site.

🔗 **Live Site:** [bkkroomie.com](https://bkkroomie.com/)

---

## ✨ What It Does

Roommate Mobile Theme is the full front-end and templating layer for a classifieds-style platform where users can:

- **Browse & post room listings** — search for available rooms or list your own
- **Browse & post roommate listings** — find compatible roommates or advertise as one
- **Manage a personal dashboard** — track your own posts and activity
- **Edit their profile** — update personal info and preferences
- **Message other users** — in-platform messaging between prospective roommates
- **View detailed listing pages** — dedicated single-room and single-roommate pages with full details

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| CMS / Framework | WordPress (custom theme, built from the WordPress Template Hierarchy) |
| Language | PHP |
| Styling | Custom CSS (mobile-first) |
| Structure | Custom page templates, archive templates, and single-post templates |

---

## 📂 Key Templates

| File | Purpose |
|---|---|
| `front-page.php` | Homepage layout |
| `archive-room.php` / `archive-roommate.php` | Listing feed pages (browse all rooms / roommates) |
| `single-room.php` / `single-roommate.php` | Individual listing detail pages |
| `post-a-room.php` / `post-a-roommate.php` | Forms for creating new listings |
| `edit-room.php` / `edit-roommate.php` | Forms for editing existing listings |
| `page-dashboard.php` | User dashboard |
| `page-edit-profile.php` | Profile management |
| `page-messages.php` | In-platform messaging |
| `functions.php` | Theme setup, custom post types, and core logic |

---

## ⚙️ Setup

This is a WordPress theme, not a standalone app. To run it locally:

1. Set up a local WordPress environment (e.g. [LocalWP](https://localwp.com/), XAMPP, or Docker)
2. Copy this repository into `wp-content/themes/roommate-mobile-theme`
3. Activate the theme from the WordPress admin dashboard
4. Configure the custom post types (rooms, roommates) as defined in `functions.php`

---

## 📬 Contact

Interested in this project or want to know more about the build? Feel free to reach out — links in my [profile README](../../).
