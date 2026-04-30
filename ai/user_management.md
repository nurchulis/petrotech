Build a complete **User Management System** using **Laravel (latest version)** with **Spatie Laravel Permission** package.

### 🎯 Goals

Create a production-ready module to manage:

* Users
* Roles
  With clean architecture, scalable structure, and best practices.

---

### 📦 Core Features

#### 1. Role Management

* CRUD Roles
* Assign permissions to roles
* Validation (unique role name)
* Pagination, search, and filter

#### 2. User Management

* CRUD Users
* Assign roles to users
* Display user and roles
* Pagination, search, and filter

  #### 3. Access Control

  * Only super_admin can manage roles
* Only admin and super_admin can manage users

---

### 🧱 Database Design

Use Spatie default tables:

* roles
* permissions
* model_has_roles
* model_has_permissions
* role_has_permissions

Also include:

* users (default Laravel)

---

### 🗂️ Structure

* Controllers: RBAC/UserController, RBAC/RoleController
* Request Validation in Controller
* Service layer : RBAC/UserService.php, RBAC/RoleService.php
* Policies: UserPolicy, RolePolicy
* 
* Clean separation of logic

---

### 🎨 UI Pages

* Users List + Create/Edit/Delete/View
* Roles List + Create/Edit/Delete/View

Use reusable Blade components.

---

### 🎨 UI Sidebar
* User Management menu item Add under the "System"
* Role Management menu item Add under the "System"

---

### 📌 Expected Output

* Full Laravel code structure
* Controllers + Routes
* Service layer (optional but preferred)
* Models
* Policies
* Blade views (basic UI)
* Step-by-step setup instructions

---

### 🚀 Bonus (if possible)

* Use DataTables or simple table enhancement
* Flash messages (success/error)
* Clean reusable layout

---

Generate clean, readable, well-structured code with comments where necessary.
