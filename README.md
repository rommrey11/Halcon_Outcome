cat << 'EOF' > README.md
# HALCON — Order Tracking & Logistics Management System

**HALCON** is a full-stack web application designed to manage the complete lifecycle of customer orders across internal departments (**Sales**, **Warehouse**, **Purchasing**, and **Route**) while offering a real-time public order tracking portal with photographic delivery evidence for customers.

* **Institution:** Universidad Tecmilenio — Campus Ferrería
* **Course:** Web Application Design
* **Student:** Rommel Reyes Jurado (ID: 2949651)
* **Teacher:** Viridiana Mares Rodríguez

---

## Table of Contents

1. [Project Overview & Architecture](#1-project-overview--architecture)
2. [Technologies Used](#2-technologies-used)
3. [Prerequisites](#3-prerequisites)
4. [Step-by-Step Installation Instructions](#4-step-by-step-installation-instructions)
5. [Environment Configuration (.env)](#5-environment-configuration-env)
6. [Database & Storage Setup](#6-database--storage-setup)
7. [Default Administrator & Departmental Users Setup](#7-default-administrator--departmental-users-setup)
8. [Building & Running the Application](#8-building--running-the-application)
9. [User Roles & Permissions Matrix](#9-user-roles--permissions-matrix)
10. [Complete Order Lifecycle & Business Workflow](#10-complete-order-lifecycle--business-workflow)
11. [Main REST API Endpoints](#11-main-rest-api-endpoints)
12. [Security & Repository Guidelines](#12-security--repository-guidelines)

---

## 1. Project Overview & Architecture

The application is built as a Single Page Application (SPA) powered by **Vue 3** consuming a **Laravel REST API** backed by **SQLite**:

* **Public Portal (`/`):** Allows customers to query their order status in real time using their **Customer Number** and **Invoice Number**. Displays a 4-stage visual progress stepper and photographic delivery evidence once the order reaches `Delivered`.
* **Administrative Portal (`/admin`):** Restricted to registered, active HALCON employees. Customers cannot self-register or access administrative screens. Features role-based navigation, employee management, active order CRUD, logical deletion/restoration, and status lifecycle transitions.

---

## 2. Technologies Used

* **Backend Framework:** PHP 8.2+ & Laravel 11/12 (RESTful API, Eloquent ORM, Validation)
* **Frontend Framework:** Vue 3 (Composition API `<script setup>`)
* **Styling:** Tailwind CSS
* **Asset Bundler:** Vite
* **Database Engine:** SQLite (`database/database.sqlite`)
* **File Storage:** Laravel Public Disk (`storage/app/public/evidences` linked to `public/storage`)

---

## 3. Prerequisites

Ensure your local machine or cloud environment (e.g., GitHub Codespaces) has the following installed:

* **PHP** >= 8.2 (with `pdo_sqlite`, `sqlite3`, `mbstring`, `fileinfo`, and `openssl` extensions enabled)
* **Composer** >= 2.x
* **Node.js** >= 18.x & **npm** >= 9.x
* **Git**

---

## 4. Step-by-Step Installation Instructions

1. **Clone the repository**
   ```bash
   git clone [https://github.com/rommrey11/Halcon_Outcome.git](https://github.com/rommrey11/Halcon_Outcome.git)
   cd Halcon_Outcome