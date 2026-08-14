# Reporting Domain

## Responsibility

Aggregates data for statistics, generates reports, and provides dashboard indicators. Read-only data aggregation.

## What belongs here

- Statistics calculation services
- Report generation actions
- Dashboard indicator data
- DTOs for report data
- Action classes for data aggregation

## What does not belong here

- Beneficiary data storage (Beneficiaries domain)
- Aid request lifecycle (AidRequests domain)
- Eligibility scoring logic (Eligibility domain)
- Authentication and roles (Identity domain)

## How it communicates with other domains

- Reads data from Beneficiaries, AidRequests, and Eligibility domains.
- Provides aggregated statistics for the admin dashboard.
- Does not modify data from other domains.