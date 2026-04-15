# Employee ID (NIK) Generation Scheme

## Format

```
{COMPANY3}{SITE2}{YY}{MM}{SEQ5}
```

### Components

- **COMPANY3**: 3-character company code prefix (uppercase alphanumeric, pad with 'X' if shorter)
  - Example: `AAP` → from company code "AAP..."
  
- **SITE2**: 2-digit site code
  - Can be numeric: `06` 
  - Or derived from site name per mapping table (see below)
  
- **YY**: 2-digit year of hire (reset sequence per year)
  - Example: `26` for 2026
  
- **MM**: 2-digit month of hire
  - Example: `04` for April
  
- **SEQ5**: 5-digit sequential number per year
  - Resets to `00001` each January
  - Incremented for each hire in that month
  - Example: `00200` for the 200th hire in 2026

## Example NIK

```
AAP06260400200
├─ AAP     Company (Andalan)
├─ 06      Site code (IBP)
├─ 26      Year (2026)
├─ 04      Month (April)
└─ 00200   Sequential number
```

## Site Code Mapping (Fallback)

| Site Name        | Kode | Code |
|------------------|------|------|
| Head Office      | 01   | HO   |
| BGG - Lahat      | 02   | BGG  |
| SBS - Tanjung Enim | 03 | SBS  |
| DBK - Murung Raya | 04  | DBK  |
| POS - Halmahera  | 05   | POS  |
| IBP - Kapuas     | 06   | IBP  |

## Implementation Location

- **File**: `app/Http/Controllers/ApplicationController.php`
- **Method**: `makeNik(Job $job, int $algo = 1): string`
- **When Called**: Automatically when application status changes to "hired"

## Notes

- NIK generation is one-time only (checks if `user.id_employe` is empty)
- Collision retry: attempts up to 5 times if generated NIK already exists
- Site code must be configured in `$siteMap` mapping for named sites, or stored as 2-digit numeric directly
