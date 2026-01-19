# Rased - Energy Market Management System

A comprehensive digital platform for managing electrical generators and operators in Palestine. The system provides complete management of operator data, generators, operational logs, maintenance, environmental compliance, and complaints/suggestions.

---

## 📋 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation & Setup](#-installation--setup)
- [Project Structure](#-project-structure)
- [Roles & Permissions](#-roles--permissions)
- [Database](#-database)
- [Development](#-development)

---

## ✨ Features

### 1. User Management & Permissions
- Advanced role-based system (SuperAdmin, Admin, Energy Authority, CompanyOwner, Employee, Technician)
- **Dynamic custom roles** - Create custom roles with specific permissions
- Dynamic permission management with interactive permission tree
- Permission audit logs to track permission changes
- Direct permissions, role-based permissions, and permission revocation capability
- **Dynamic role filtering** - Filter users by system roles or custom roles

### 2. Operator Management
- Comprehensive operator profile (location, capacity, owner data, etc.)
- Link operators to company owners (CompanyOwners)
- Manage employees and technicians for each operator
- Operator profile completion tracking system

### 3. Generator Management
- Complete technical data (capacity, voltage, frequency, engine type)
- Operating and fuel information
- Technical status and documentation (data plate images)
- Control system (control panel, status, images)
- External fuel tank management

### 4. Records & Reports
- Operation logs
- Fuel efficiency tracking
- Maintenance records
- Environmental compliance & safety

### 5. Complaints & Suggestions System
- Public interface for submitting complaints and suggestions
- Unique tracking code system
- Link complaints to generators and operators

### 6. Dynamic Constants System
- Manage system constants from database
- Support for governorates, engine types, generator statuses, etc.
- Easy addition and modification of constants

### 7. Recent Improvements
- **Dynamic Role System**: Support for both system roles (4 roles) and custom roles created by users
- **Role Filter Enhancement**: Users filter now supports filtering by custom roles dynamically
- **Role Show Page Redesign**: Redesigned role details page using general-card component
- **Collapsible Permission Cards**: Added toggle functionality to permission group cards

---

## 📦 Requirements

- **PHP**: ^8.2
- **Laravel**: ^12.0
- **MySQL/MariaDB**: 10.3 or later
- **Node.js**: 18.x or later
- **Composer**: 2.x
- **npm** or **yarn**

---

## 🚀 Installation & Setup

### 1. Clone the Project

```bash
git clone <repository-url>
cd rased
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

Update `.env` file with database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rased
DB_USERNAME=root
DB_PASSWORD=
```

Then run migrations:

```bash
php artisan migrate
```

### 5. Seed Database

```bash
# Run seeders
php artisan db:seed --class=ConstantSeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder

# (Optional) Seed test data
php artisan db:seed --class=OperatorsWithDataSeeder
```

### 6. Create Storage Link

```bash
php artisan storage:link
```

### 7. Run the Application

#### Development Environment:

```bash
# Run server and Vite together
npm run dev

# Or separately:
php artisan serve        # Server at http://127.0.0.1:8000
npm run dev              # Vite for assets
```

#### Production Environment:

```bash
# Build assets
npm run build

# Run server
php artisan serve
```

---

## 📁 Project Structure

```
rased/
├── app/
│   ├── Governorate.php              # Enum for governorates
│   ├── Role.php                     # Enum for roles
│   ├── Helpers/
│   │   ├── ConstantsHelper.php      # Constants helper
│   │   └── GeneralHelper.php        # General helper (operators by governorate)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # Admin panel controllers
│   │   │   └── ComplaintSuggestionController.php
│   │   ├── Middleware/
│   │   │   └── AdminMiddleware.php  # Admin permission middleware
│   │   └── Requests/               # Form validation requests
│   ├── Mail/
│   │   └── OperatorCredentialsMail.php
│   ├── Models/                      # Eloquent models
│   └── Policies/                    # Authorization policies
├── database/
│   ├── migrations/                  # Database migrations
│   └── seeders/                     # Database seeders
├── public/
│   └── assets/
│       └── admin/                   # Admin panel assets
│           ├── css/
│           ├── js/
│           │   ├── general-helpers.js  # JavaScript helper functions
│           │   ├── permissions.js      # Permissions page logic
│           │   └── generators.js       # Generators page logic
│           └── images/
├── resources/
│   ├── views/
│   │   ├── admin/                  # Admin panel views
│   │   ├── auth/                   # Authentication views
│   │   └── complaints-suggestions/ # Complaints & suggestions views
│   ├── css/
│   └── js/
└── routes/
    ├── admin.php                   # Admin panel routes
    └── web.php                     # Public routes
```

---

## 👥 Roles & Permissions

### Available Roles:

1. **SuperAdmin** (System Administrator)
   - Full access to all functions
   - User and role management
   - Constants management

2. **Admin** (Administrator)
   - Operator and generator management
   - View reports
   - Can create general custom roles

3. **Energy Authority** (سلطة الطاقة)
   - Can create general and operator-specific custom roles
   - User management under their authority
   - Operator approval and management

4. **CompanyOwner** (Company Owner)
   - Manage their own operator
   - Manage employees and technicians
   - Manage generators
   - Manage employee permissions
   - Can create custom roles for their operator

5. **Employee** (Employee)
   - View data according to granted permissions
   - Enter operation logs

6. **Technician** (Technician)
   - View and enter generator data
   - Manage maintenance records

### Permission System:

- **Direct Permissions**: Granted directly to users
- **Role Permissions**: Granted through roles (system roles or custom roles)
- **Revoked Permissions**: Ability to revoke specific permissions even if granted through role

### Role System:

- **System Roles**: 4 predefined system roles (SuperAdmin, Admin, Energy Authority, CompanyOwner) stored in database
- **Custom Roles**: Dynamic roles created by Energy Authority or Company Owner with custom permissions
- **Role Filtering**: Filter users by system roles or custom roles dynamically

---

## 🗄️ Database

### Main Tables:

- `users`: Users
- `operators`: Operators
- `generators`: Generators
- `fuel_tanks`: Fuel tanks
- `operation_logs`: Operation logs
- `fuel_efficiencies`: Fuel efficiency
- `maintenance_records`: Maintenance records
- `compliance_safeties`: Environmental compliance & safety
- `permissions`: Permissions
- `roles`: Roles (system and custom)
- `role_permission`: Role-permission pivot table
- `user_permission`: Direct user permissions
- `user_permission_revoked`: Revoked user permissions
- `constant_masters`: Constant masters
- `constant_details`: Constant details
- `complaints_suggestions`: Complaints and suggestions

### Relationships:

- `User` → `Operator` (owner: belongsTo)
- `User` ↔ `Operator` (many-to-many: employees/technicians)
- `User` → `Role` (role_id: belongsTo for custom roles)
- `Role` → `Permission` (many-to-many: role_permission)
- `Operator` → `Generator` (hasMany)
- `Generator` → `FuelTank` (hasMany)
- `Generator` → `OperationLog` (hasMany)
- `Generator` → `MaintenanceRecord` (hasMany)

---

## 💻 Development

### Recent Improvements:

#### 1. Dynamic Role System
- Added support for custom roles alongside system roles
- System roles are stored in database (4 roles: SuperAdmin, Admin, Energy Authority, CompanyOwner)
- Custom roles can be created by Energy Authority or Company Owner
- Custom roles can be general (operator_id = null) or operator-specific (operator_id = specific operator)

#### 2. Enhanced User Filtering
- Updated user filtering to support both system roles (enum) and custom roles (from roles table)
- Role filter dynamically loads available roles based on user permissions
- Filter displays system roles first, then custom roles

#### 3. Role Show Page Redesign
- Redesigned role details page using `general-card` component
- Added statistics cards for users count, permissions count, permission groups count, and order
- Separated basic information and permissions into distinct cards
- Added collapsible permission group cards with toggle functionality

### Helper Functions

#### PHP Helpers

**ConstantsHelper** - Constants management:
```php
use App\Helpers\ConstantsHelper;

// Get constant by name
$governorates = ConstantsHelper::getByName('المحافظة');

// Get constant by number
$statuses = ConstantsHelper::get(3); // Generator status
```

**GeneralHelper** - General functions:
```php
use App\Helpers\GeneralHelper;

// Get operators by governorate
$operators = GeneralHelper::getOperatorsByGovernorate(10); // Gaza governorate
```

#### JavaScript Helpers

**GeneralHelpers** - Available in all admin pages:
```javascript
// Get operators by governorate
GeneralHelpers.getOperatorsByGovernorate(10)
    .then(operators => console.log(operators));

// Fill select with operators
GeneralHelpers.fillOperatorsSelect(10, '#operator-select');

// With jQuery
$('#operator-select').fillOperatorsByGovernorate(10);
```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter TestName
```

---

## 📝 Important Notes for Developers

### 1. Permission System

- **Always use Policies** for permission checks in Controllers
- Use `@can` directive in Blade templates
- Ensure CompanyOwner can only access their own employees and generators
- Support both system roles (enum) and custom roles (role_id)

### 2. RTL (Right-to-Left)

- Project uses RTL layout
- Ensure Bootstrap RTL is used
- In CSS, use `direction: rtl`

### 3. Constants

- **Do not hardcode values** in code
- Always use `ConstantsHelper` to get values
- Add new constants in the seeder

### 4. AJAX Requests

- Use jQuery AJAX or Fetch API
- Use `showToast` to display messages (from `toast.blade.php`)
- Ensure JSON responses are returned in AJAX endpoints

### 5. Form Validation

- Use Form Requests (`app/Http/Requests/`)
- Add clear Arabic error messages
- Use `old()` in views to retain values on errors

### 6. Relationships

- Use Eager Loading to avoid N+1 queries
- Example: `Generator::with('operator', 'fuelTanks')->get()`

### 7. Images & Files

- Images are saved in `storage/app/public/`
- Use `php artisan storage:link` to create symbolic link
- Use `asset('storage/...')` in views

---

## 🔧 Troubleshooting

### Permission Issues

```bash
# Clear permission cache
php artisan cache:clear
php artisan config:clear
```

### Constants Issues

```bash
# Clear constants cache
php artisan tinker
>>> App\Helpers\ConstantsHelper::clearCache();
```

### Assets Issues

```bash
# Rebuild assets
npm run build
php artisan optimize:clear
```

---

## 📚 Useful Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel 12 Release Notes](https://laravel.com/docs/releases)
- [Bootstrap RTL](https://getbootstrap.com/docs/5.3/getting-started/rtl/)

---

## 🤝 Contributing

1. Fork the project
2. Create a new branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License.

---

## 👨‍💻 Support

For any questions or issues, please open an issue in the project.

---

**Built with ❤️ using Laravel 12**
