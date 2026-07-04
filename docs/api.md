# HirePilot API Plan

## Core endpoints
- GET /api/health - health check for the platform
- POST /api/auth/register - user registration
- POST /api/auth/login - user sign-in
- POST /api/auth/logout - user sign-out
- GET /api/jobs - list jobs
- POST /api/jobs/import - import jobs from providers
- GET /api/resumes - list resumes
- POST /api/resumes - create a resume
- POST /api/resumes/{id}/tailor - tailor a resume to a job
- POST /api/cover-letters - generate a cover letter

## Standards
- JSON responses with success, message, and data
- Validation via Form Requests
- Pagination and filtering for list endpoints
- Rate limiting and policy-based authorization
