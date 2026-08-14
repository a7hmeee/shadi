# Eligibility Domain

## Responsibility

Calculates eligibility scores, classifies priority, detects duplicates, and provides decision support recommendations.

## What belongs here

- Eligibility scoring rules
- Priority classification logic
- Duplicate and overlap detection
- Decision support recommendation services
- DTOs for eligibility data
- Action classes for eligibility checks

## What does not belong here

- Beneficiary data storage (Beneficiaries domain)
- Aid request lifecycle (AidRequests domain)
- Authentication and roles (Identity domain)
- Reporting and statistics (Reporting domain)

## How it communicates with other domains

- Reads beneficiary data from Beneficiaries domain.
- Receives aid request data from AidRequests domain for scoring.
- Provides scoring results to AidRequests domain for decision-making.
- Reporting domain consumes eligibility data for statistics.