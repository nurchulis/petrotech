Build a **VDI / VM Access Management Module** using **Laravel (latest version)** and **MySQL**.

The system must allow assigning access to Virtual Machines (VDI) based on:

1. Individual Users
2. Groups / Divisions

The goal is to control which users can access which VMs in a flexible and scalable way.

---

## 🎯 Core Requirements

### 1. VM Management

* CRUD Virtual Machines
* Fields:

  * name
  * ip_address
  * description
  * status (active/inactive)

---

### 2. Group / Division Management

* CRUD Groups (e.g., IT, Finance, HR)
* Assign users to groups

---

### 3. User Access Assignment

#### A. Direct User Access

* Assign multiple VMs directly to a user
* Many-to-many relationship

#### B. Group-Based Access

* Assign multiple VMs to a group
* All users in the group inherit access

---

### 4. Access Resolution Logic

When retrieving accessible VMs for a user:

* Combine:

  * Direct user VM access
  * Group-based VM access
* Remove duplicates
* Only include active VMs

---

### 5. Access Control Rules

* Admin can manage all
* Non-admin users can only view VMs they have access to

---

## 🧱 Database Design

Tables:

* users (default Laravel)
* vms
* groups
* group_user (pivot)
* user_vm_access (pivot)
* group_vm_access (pivot)

---

## ⚙️ Relationships (Eloquent)

* User:

  * belongsToMany(Group)
  * belongsToMany(VM) → direct access

* Group:

  * belongsToMany(User)
  * belongsToMany(VM)

* VM:

  * belongsToMany(User)
  * belongsToMany(Group)

---

## 🧠 Business Logic

Create a method:

getAccessibleVMs(User $user)

Steps:

1. Get VM IDs from user_vm_access
2. Get user's groups
3. Get VM IDs from group_vm_access
4. Merge both
5. Remove duplicates
6. Filter only active VMs
7. Return collection

---

## 🎨 UI Pages (Blade)

* VM List + Create/Edit
* Group List + Assign Users
* User List + Assign VM Access
* Group VM Assignment Page
* User Accessible VM Dashboard

---

## 🔐 Authorization

* Use Laravel Policies or Gates
* Example:

  * Only admin can assign VM access
  * Users can only view their accessible VMs

---

## 🧪 Seeder

Create:

* Admin user
* Sample groups (IT, Finance)
* Sample VMs
* Sample access mapping

---

## 📌 Expected Output

* Migrations
* Models with relationships
* Controllers (VM, Group, UserAccess)
* Service class for access logic
* Routes (web.php)
* Blade views (basic UI)
* Seeder
* Clean, maintainable code with comments

---

## 🚀 Bonus (Optional but Preferred)

* Add "expires_at" on access (temporary VM access)
* Add activity logs (who accessed which VM)
* Add search & filter on VM list
* Use UUID for VMs
* Add API endpoints (optional)

---

Generate clean, scalable, production-ready Laravel code following best practices.
