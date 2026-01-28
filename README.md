# Nour - Energy Market Management System

A comprehensive digital platform for managing electrical generators and operators in Palestine. The system provides complete management of operator data, generators, operation logs, maintenance, environmental compliance, and complaints & suggestions.

**Repository**: [https://github.com/tareeqemad/nour](https://github.com/tareeqemad/nour)

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Project Structure](#project-structure)
- [Roles & Permissions](#roles--permissions)
- [Database](#database)
- [Development Guide](#development-guide)
- [API Documentation](#api-documentation)
- [Contributing](#contributing)
- [License](#license)

---

## Features

### User Management & Permissions
- Advanced role-based access control system
- Dynamic custom roles with granular permissions
- Interactive permission tree management
- Permission audit logs
- Direct permissions, role-based permissions, and permission revocation

### Operator Management
- Comprehensive operator profiles
- Link operators to company owners
- Employee and technician management
- Profile completion tracking

### Generator Management
- Complete technical specifications
- Operating and fuel information
- Technical status and documentation
- Control system management
- External fuel tank tracking

### Records & Reports
- Operation logs
- Fuel efficiency tracking
- Maintenance records
- Environmental compliance & safety

### Complaints & Suggestions System
- Public interface for submissions
- Unique tracking codes
- Link complaints to generators and operators

### Dynamic Constants System
- Database-driven constants management
- Support for governorates, engine types, statuses, etc.
- Easy addition and modification

### Internal Messaging System
- Advanced messaging with forwarding, CC/BCC support
- Message starring and importance marking
- Archiving functionality
- Advanced search filters
- Real-time unread message count

---

## Requirements

- **PHP**: ^8.2
- **Laravel**: ^12.0
- **MySQL/MariaDB**: 10.3 or later
- **Node.js**: 18.x or later
- **Composer**: 2.x
- **npm** or **yarn**

---

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/tareeqemad/nour.git
cd nour
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nour
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Database Setup

```bash
php artisan migrate
php artisan db:seed --class=ConstantSeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
```

### 5. Storage Link

```bash
php artisan storage:link
```

### 6. Run the Application

**Development:**
```bash
npm run dev
```

**Production:**
```bash
npm run build
php artisan serve
```

---

## Project Structure

```
nour/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   └── Api/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Policies/
│   ├── Services/
│   └── Helpers/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   └── assets/
├── resources/
│   ├── views/
│   ├── css/
│   └── js/
└── routes/
    ├── admin.php
    └── web.php
```

---

## Roles & Permissions

### System Roles

1. **SuperAdmin**
   - Full system access
   - User and role management
   - Constants management

2. **Admin**
   - Operator and generator management
   - Report viewing
   - General custom role creation

3. **Energy Authority**
   - General and operator-specific custom roles
   - User management under authority
   - Operator approval and management

4. **CompanyOwner**
   - Own operator management
   - Employee and technician management
   - Generator management
   - Employee permission management
   - Custom role creation for operator

5. **Employee**
   - View data based on permissions
   - Enter operation logs

6. **Technician**
   - View and enter generator data
   - Manage maintenance records

### Permission System

- **Direct Permissions**: Granted directly to users
- **Role Permissions**: Granted through roles (system or custom)
- **Revoked Permissions**: Explicitly revoked permissions override role permissions

---

## Database

### Main Tables

- `users` - User accounts
- `operators` - Operator entities
- `generators` - Generator records
- `fuel_tanks` - Fuel tank information
- `operation_logs` - Operation history
- `fuel_efficiencies` - Fuel efficiency data
- `maintenance_records` - Maintenance history
- `compliance_safeties` - Compliance and safety records
- `permissions` - System permissions
- `roles` - System and custom roles
- `role_permission` - Role-permission relationships
- `user_permission` - Direct user permissions
- `user_permission_revoked` - Revoked permissions
- `constant_masters` - Constant categories
- `constant_details` - Constant values
- `complaints_suggestions` - Public complaints and suggestions

### Key Relationships

- User → Operator (owner relationship)
- User ↔ Operator (many-to-many for employees/technicians)
- User → Role (custom role assignment)
- Role → Permission (many-to-many)
- Operator → Generator (hasMany)
- Generator → FuelTank, OperationLog, MaintenanceRecord (hasMany)

---

## Development Guide

### Helper Functions

**PHP Helpers:**

```php
use App\Helpers\ConstantsHelper;
use App\Helpers\GeneralHelper;

// Get constants
$governorates = ConstantsHelper::getByName('المحافظة');
$statuses = ConstantsHelper::get(3);

// Get operators by governorate
$operators = GeneralHelper::getOperatorsByGovernorate(10);
```

**JavaScript Helpers:**

```javascript
// Get operators by governorate
GeneralHelpers.getOperatorsByGovernorate(10)
    .then(operators => console.log(operators));

// Fill select dropdown
GeneralHelpers.fillOperatorsSelect(10, '#operator-select');
```

### Best Practices

1. **Use Form Requests** for validation
2. **Use Service Classes** for business logic
3. **Use Policies** for authorization
4. **Use Eager Loading** to avoid N+1 queries
5. **Use ConstantsHelper** instead of hardcoding values
6. **Use `@can` directive** in Blade templates
7. **Cache frequently accessed data**

### Cascading Selects Component

The cascading selects component allows sequential selection of Operator → Generation Unit → Generator.

**Usage:**

```blade
@include('admin.partials.cascading-selects', [
    'operators' => $operators,
    'affiliatedOperator' => $affiliatedOperator,
    'canSelectOperator' => $canSelectOperator,
    'showGenerator' => true,
    'showGenerationUnit' => true,
])

@push('scripts')
    @include('admin.partials.cascading-selects-scripts', [
        'canSelectOperator' => $canSelectOperator,
        'affiliatedOperatorId' => $affiliatedOperator?->id,
    ])
@endpush
```

**Required Routes:**
- `GET /admin/operators/{operator}/generation-units`
- `GET /admin/generation-units/{generationUnitId}/generators-list`

---

## API Documentation

### Base URL

```
https://gazarased.com/api
```

### Authentication

All protected endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer {token}
```

### Endpoints

#### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get current user info

#### QR Code Scanning
- `POST /api/qr/scan` - Scan generator QR code

#### Maintenance Records (Technicians)
- `GET /api/maintenance/form-data/{generator}` - Get form data
- `POST /api/maintenance/store` - Create maintenance record
- `GET /api/maintenance/records` - List maintenance records
- `GET /api/maintenance/records/{id}` - Get specific record

#### Compliance Safety (Civil Defense)
- `GET /api/compliance-safety/form-data/{generator}` - Get form data
- `POST /api/compliance-safety/store` - Create compliance record
- `GET /api/compliance-safety/records` - List compliance records
- `GET /api/compliance-safety/records/{id}` - Get specific record

### Error Responses

All errors follow this format:

```json
{
    "success": false,
    "message": "Error message in Arabic"
}
```

**Status Codes:**
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

### API Setup

**Install Laravel Sanctum:**

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

**Update User Model:**

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, ...;
}
```

---

## Troubleshooting

### Permission Issues

```bash
php artisan cache:clear
php artisan config:clear
```

### Constants Cache

```bash
php artisan tinker
>>> App\Helpers\ConstantsHelper::clearCache();
```

### Assets Issues

```bash
npm run build
php artisan optimize:clear
```

---

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## License

This project is licensed under the MIT License.

---

## Support

For questions or issues, please open an issue in the repository: [https://github.com/tareeqemad/nour/issues](https://github.com/tareeqemad/nour/issues)

---

**Built with Laravel 12**
