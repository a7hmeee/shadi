# Beneficiaries Domain

## Responsibility

Manages beneficiary data, family information, and socio-economic profiles.

## What belongs here

- Beneficiary model
- Family model
- Socio-economic data structures
- Actions for creating and updating beneficiary records
- DTOs for beneficiary data transfer
- Repositories for beneficiary persistence
- Policies for beneficiary access control

## What does not belong here

- Authentication and roles (Identity domain)
- Aid request processing (AidRequests domain)
- Eligibility scoring (Eligibility domain)
- Reporting and statistics (Reporting domain)

## How it communicates with other domains

- Uses PermissionName enum for authorization checks.
- AidRequests domain references beneficiaries when creating or reviewing requests.
- Eligibility domain uses beneficiary data for scoring.
- Reporting domain aggregates beneficiary data for statistics.