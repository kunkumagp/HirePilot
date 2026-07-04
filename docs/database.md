# HirePilot Database Plan

## Core tables
- users
- resumes
- resume_versions
- jobs
- applications
- ai_recommendations
- job_searches
- cover_letters
- interview_questions

## Design principles
- UUIDs for public-facing identifiers where beneficial
- Foreign keys for all dependent records
- Soft deletes for core business entities
- Indexes on frequently filtered columns such as status and provider
- Transactions for import and application workflow operations
