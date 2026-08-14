# AidRequests Domain

## Responsibility

Manages aid requests, their lifecycle, review process, and status transitions.

## What belongs here

- AidRequest model
- Request status enums
- Actions for creating, reviewing, approving, rejecting, and marking requests as delivered
- DTOs for request data transfer
- Repositories for request persistence
- Policies for request access control
- Services for request workflow logic

## What does not belong here

- Beneficiary data management (Beneficiaries domain)
- Eligibility scoring (Eligibility domain)
- Authentication and roles (Identity domain)
- Reporting and statistics (Reporting domain)

## How it communicates with other domains

- References Beneficiaries domain for beneficiary data.
- Uses PermissionName enum for authorization checks.
- Eligibility domain may provide scoring to inform request decisions.
- Reporting domain aggregates request data for statistics.