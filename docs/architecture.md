# HirePilot Architecture

## Overview
HirePilot is a modular SaaS platform for AI-assisted job search and application management. The repository is split into a Laravel backend and a React frontend so each layer can evolve independently.

## Backend
- Laravel 12 + PHP 8.2
- Repository and service layer for business logic
- REST API served from the backend
- Queue jobs, scheduler, and storage abstraction for future scale
- Sanctum-ready authentication foundation

## Frontend
- React + TypeScript + Vite
- Feature-based folders under src/features
- TanStack Query for API data fetching
- Zustand for lightweight client state
- Tailwind CSS and shadcn-ready structure

## Principles
- Thin controllers
- Services for business logic
- Repositories for data access
- AI providers behind interfaces
- Strong validation and authorization
