# Manpower Dashboard - Formula Documentation

## Overview

This document contains all formulas and calculations used in the Manpower Dashboard (`/admin/dashboard/manpower`).

**Controller:** `app/Http/Controllers/ManpowerDashboardController.php`
**View:** `resources/views/admin/dashboard/manpower.blade.php`
**Cache Duration:** 30 minutes

---

## KPI Cards

### 1. Open Jobs
- **Formula:** `COUNT(jobs WHERE status = 'open')`
- **Description:** Total number of active/open job positions
- **Source:** `job_listings` table

### 2. Total Applicants
- **Formula:** `COUNT(job_applications)`
- **Description:** Total number of all job applications in the system
- **Source:** `job_applications` table

### 3. Applicants / Open Job
- **Formula:** `SUM(applicants_count across all open jobs)`
- **Description:** Accumulated number of applicants per open job
- **Source:** `job_applications` joined with `job_listings`

### 4. Fulfillment %
- **Formula:** `(sourcingOnsiteHired / sourcingOnsiteTotal) * 100`
- **Where:**
  - `sourcingOnsiteTotal = COUNT(candidate_profiles WHERE source_channel IN ('sourcing', 'onsite'))`
  - `sourcingOnsiteHired = COUNT(candidates WHERE source_channel IN ('sourcing', 'onsite') AND job_application.overall_status = 'hired')`
- **Description:** Percentage of sourcing + onsite candidates who became employees
- **Rounding:** `round()` to nearest integer

### 5. SLA Success Rate
- **Formula:** `(acceptedOlCount / activeApps) * 100`
- **Where:**
  - `acceptedOlCount = COUNT(offers WHERE status = 'accepted')`
  - `activeApps = COUNT(job_applications)`
- **Description:** Percentage of applicants who received and accepted offer letters
- **Rounding:** `round()` to nearest integer

---

## Age Insight

### Average Age
- **Formula:** `AVG(COALESCE(age, TIMESTAMPDIFF(YEAR, birthdate, CURDATE())))`
- **Description:** Average age of all candidate profiles
- **Fallback:** Uses `birthdate` to calculate age if `age` field is null

### Min Age
- **Formula:** `MIN(COALESCE(age, TIMESTAMPDIFF(YEAR, birthdate, CURDATE())))`
- **Description:** Youngest candidate age

### Max Age
- **Formula:** `MAX(COALESCE(age, TIMESTAMPDIFF(YEAR, birthdate, CURDATE())))`
- **Description:** Oldest candidate age

---

## Top Failed Stage

- **Formula:** `COUNT(application_stages WHERE status IN ('failed', 'no-show')) GROUP BY stage_key ORDER BY COUNT DESC LIMIT 1`
- **Description:** The recruitment stage with the highest number of failures/no-shows
- **Source:** `application_stages` table

---

## Overall Hiring

### Filled
- **Formula:** `COUNT(job_applications WHERE overall_status = 'hired')`
- **Description:** Total number of candidates who have been hired

### Budget Openings
- **Formula:** `SUM(openings WHERE status = 'open')`
- **Description:** Total number of open positions from all active jobs
- **Source:** `job_listings` table

---

## SLA per Level (Chart)

- **Formula:** `AVG(DATEDIFF(hired_updated_at, job_created_at))` grouped by level
- **Where:**
  - Only includes applications with `overall_status = 'hired'`
  - Calculated per job level (Foreman, Supervisor, Superintendent, Manager, etc.)
- **Description:** Average days from job posting to candidate being hired, per level
- **Filter for chart:** Only shows levels where `hired > 0` AND `avg_sla_days > 0`

---

## Applicant Source (Chart)

- **Formula:** `COUNT(candidate_profiles) GROUP BY source_channel`
- **Source Channels:**
  - `sourcing` - Sourcing
  - `onsite` - Onsite
  - `referral` - Referral
  - `linkedin` - LinkedIn
  - `instagram` - Instagram
  - `job_portal` - Job Portal
  - `other` - Lainnya
  - `unknown` - Unknown (empty/null values)
- **Description:** Distribution of where candidates came from
- **Chart Type:** Doughnut

---

## Education Background (Chart)

- **Formula:** `COUNT(candidate_profiles) GROUP BY last_education`
- **Education Levels:**
  - `SD` - SD
  - `SMP` - SMP
  - `SMA_SMK` - SMA/SMK
  - `D1` - D1
  - `D2` - D2
  - `D3` - D3
  - `D4` - D4
  - `S1` - S1
  - `S2` - S2
  - `S3` - S3
  - `LAINNYA` - Lainnya
  - `unknown` - Unknown (empty/null values)
- **Description:** Distribution of candidates by their last education level
- **Chart Type:** Bar

---

## Gender Breakdown (Chart)

- **Formula:** `COUNT(candidate_profiles) GROUP BY gender`
- **Categories:**
  - `male` - Male
  - `female` - Female
  - `other` - Other (all non-male/female values combined)
- **Description:** Distribution of candidates by gender
- **Chart Type:** Doughnut

---

## Stage Failure (Chart)

- **Formula:** `COUNT(application_stages WHERE status IN ('failed', 'no-show')) GROUP BY stage_key ORDER BY COUNT DESC`
- **Description:** Count of failed/no-show candidates per recruitment stage
- **Chart Type:** Bar

---

## Application Trend (Chart)

- **Formula:** `COUNT(job_applications) GROUP BY MONTH(created_at) WHERE YEAR(created_at) = current_year`
- **Description:** Monthly application intake for the current year (Jan-Dec)
- **Chart Type:** Line

---

## Open Jobs Table

| Column | Formula | Description |
|--------|---------|-------------|
| **Job** | `jobs.title` | Job title |
| **Level** | `Job::LEVEL_LABELS[jobs.level]` | Level label mapping |
| **Openings** | `jobs.openings` | Number of openings for this job |
| **Applicants** | `COUNT(job_applications WHERE job_id = job.id)` | Total applicants for this job |
| **Terima OL** | `COUNT(job_applications WHERE job_id = job.id AND offer.status = 'accepted')` | Applicants who accepted offer |
| **Hired** | `COUNT(job_applications WHERE job_id = job.id AND overall_status = 'hired')` | Applicants who were hired |

---

## Level Performance Summary

| Column | Formula | Description |
|--------|---------|-------------|
| **Level** | `Job::LEVEL_LABELS[level_key]` | Level name |
| **Open Jobs** | `COUNT(jobs WHERE level = level_key AND status = 'open')` | Active jobs at this level |
| **Applicants** | `COUNT(job_applications JOIN jobs WHERE jobs.level = level_key)` | Total applicants at this level |
| **Hired** | `COUNT(job_applications JOIN jobs WHERE jobs.level = level_key AND overall_status = 'hired')` | Candidates hired at this level |
| **Avg SLA (days)** | `AVG(DATEDIFF(job_application.updated_at, job.created_at))` where `overall_status = 'hired'` | Average days from job posting to hire |
| **Success Rate** | `(hired / applicants) * 100` | Percentage of applicants who got hired |

### Available Levels
1. Foreman
2. Supervisor
3. Superintendent
4. Manager
5. Analyst
6. Specialist
7. Expert
8. Lead Of
9. Section Head
10. Dept Head
11. Project Manager
12. PJO
13. Non Staff

---

## Data Flow Summary

```
Database Tables
├── job_listings (jobs)
├── job_applications (applications)
├── candidate_profiles (candidates)
├── application_stages (stage tracking)
├── offers (offer letters)
└── pohs (places of acceptance)
        ↓
ManpowerDashboardController
├── Queries all tables
├── Calculates metrics
└── Caches for 30 minutes
        ↓
manpower.blade.php
├── Displays KPI cards
├── Renders Chart.js charts
└── Shows data tables
```

---

## Notes

- All data is cached for **30 minutes** under cache key `dashboard.manpower`
- Cache is cleared automatically in unit tests
- If required tables don't exist, empty metrics are returned
- SLA calculations use `Carbon::diffInDays()` for day difference
- Missing ages are calculated from `birthdate` using `TIMESTAMPDIFF(YEAR, birthdate, CURDATE())`
