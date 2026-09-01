# Ágape System

Management system for a psychiatric clinic in Curitiba: patient records,
appointment scheduling and clinical records. Built by a 4-person team as a
university extension project at PUCPR (100h), delivered to the clinic.

## Stack

- Laravel + PostgreSQL
- REST API consumed by a TypeScript/Vite SPA
- Deployed on Oracle Cloud (API) and Vercel (frontend)

## Access control and auditing

Role-based access control with 4 roles. Every read and write on clinical
records is logged. The system handles sensitive health data, so LGPD
compliance shaped the data model from the start.

## My scope

Backend and data modeling through to production deployment.

## Status

Delivered to the clinic in 2026.
