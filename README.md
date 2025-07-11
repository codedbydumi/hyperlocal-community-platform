---
# 🌐 Hyperlocal Community Platform

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4+-8892BF.svg?style=flat&logo=php">
  <img src="https://img.shields.io/badge/MySQL-5.7+-00758F.svg?style=flat&logo=mysql">
  <img src="https://img.shields.io/badge/License-MIT-yellow.svg">
  <img src="https://img.shields.io/badge/Status-Active-brightgreen.svg">
  <img src="https://img.shields.io/badge/Made%20with-%E2%9D%A4-red">
</p>

---

## 📖 Overview

The **Hyperlocal Community Platform** is a PHP-based web application built to connect neighborhoods and foster meaningful local interactions. 

Its **core functionality is a Rent Item & Services system**, allowing users to rent or offer everyday items and services like:
- 🍼 Baby Strollers  
- 🏕️ Camping Tents  
- 🧹 House Cleaning  
- 🧑‍🏫 Private Tutoring  
- 🐕 Dog Walking  

In addition to rentals, users can chat in real-time, report issues, manage groups, and share neighborhood updates — all in one place.

---

## 🌟 Key Features

### 🛍️ Rent Item & Services
- Post items/services to rent or borrow from neighbors
- Browse by category (tools, electronics, services, etc.)
- Confirm, manage, and track orders in real-time
- In-app notifications for each rental update

### 💬 Community Interaction
- Public and group chat walls with real-time messaging
- Create or join neighborhood-based groups
- Share ideas, organize events, and collaborate locally

### 📊 User Dashboard
- Personal panel to view, create, and manage:
  - Listings  
  - Orders  
  - Group memberships  
  - Chats  
  - Notifications

### 🧾 Reporting & Admin Tools
- Users can report inappropriate posts or items
- Admin dashboard for user and listing moderation
- Status tracking for reported issues

---

## 🗂️ Folder Structure

```

/source-code/        → Full project source (PHP, JS, CSS)
/assets/             → Screenshots, demo video
/database/           → SQL file for DB import
README.md            → Project guide and documentation
LICENSE              → Open-source MIT license

````

---

## 🚀 Getting Started

### ✅ Requirements
- PHP 7.4 or higher  
- MySQL 5.7+  
- Apache or Nginx (XAMPP/WAMP recommended)  
- Composer (optional)

### ⚙️ Setup Instructions

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/hyperlocal-community-platform.git
   cd hyperlocal-community-platform

2. **Set Up the Database**

   * Use phpMyAdmin or MySQL CLI
   * Create a new database
   * Import the file from `/database/database.sql`

3. **Configure Database Credentials**

   * Open `db.php` or your config file
   * Enter your local MySQL credentials

4. **Run the Project**

   * Place the `/source-code/` in your local server’s root directory (`htdocs/` for XAMPP)
   * Start Apache & MySQL
   * Open in browser:
     `http://localhost/source-code/`

---


## 📸 Screenshots & Demo

* 📷 **Screenshots:**
  Located in `/assets/screenshots/`

* 🎥 **Video Walkthrough:**
  Find the demo video at `/assets/demo.mp4`

---

## 🛠️ Tech Stack

* **Backend:** PHP
* **Database:** MySQL
* **Frontend:** HTML5, CSS3, Bootstrap 4, JavaScript, jQuery
* **Icons:** FontAwesome
* **Realtime Chat:** AJAX-based group messaging

---

## 🤝 Contributing

We welcome contributions to enhance the platform!

1. Fork this repo
2. Create a feature branch: `git checkout -b feature/YourFeature`
3. Commit your changes: `git commit -m "Added new feature"`
4. Push the branch: `git push origin feature/YourFeature`
5. Open a Pull Request 🚀

---

## 📄 License

This project is licensed under the **MIT License**.
See the [LICENSE](LICENSE) file for full details.

---

## 👨‍💻 Author

**Made with ❤️ by [codedbydumi](https://github.com/codedbydumi)**
Feel free to connect or reach out for collaboration, improvements, or ideas!

---




