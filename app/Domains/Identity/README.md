# Identity Domain

## Responsibility

Manages user authentication, authorization, roles, and permissions. Controls access to the admin panel.

## What belongs here

- User model (authentication)
- Role enums (UserRole)
- Permission enums (PermissionName)
- Role and permission seeding
- Gate bypass logic for super_admin
- Policies related to user access

## What does not belong here

- Beneficiary data
- Aid request logic
- Eligibility scoring
- Reporting statistics

## How it communicates with other domains

- Provides the User model and role/permission infrastructure used by all other domains.
- Other domains reference UserRole and PermissionName enums for authorization checks.
- Filament uses the User model's `canAccessPanel` method for panel access control.