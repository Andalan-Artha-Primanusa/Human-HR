# Human-HR Project Guidelines

## Code Style
- Follow Laravel conventions: PSR-4 autoloading, camelCase for methods/variables, PascalCase for classes
- Use UUID primary keys for all main models (via `HasUuidPrimaryKey` trait)
- Email addresses are stored lowercased (mutator in User model)
- Controllers use resource naming: `JobController`, `CandidateProfileController`
- Views use Blade templating with consistent naming: `jobs.index`, `admin.users.edit`

## Architecture
- **Framework**: Laravel 12 with Laravel signed-link email verification
- **Authentication**: Users have roles (pelamar|hr|superadmin) stored in `User.role` column
- **Models**: 20+ domain models including Job, CandidateProfile, JobApplication, Interview, Offer
- **Admin Panel**: Separate `Admin/` namespace with `/admin` route prefix, requires `role:hr|superadmin` middleware
- **Workflow**: Kanban-style job application stages managed via `ApplicationStage` model
- **Services**: `IcsService` for generating calendar invites, custom email services
- **Database**: Multi-company support, extensive candidate profiling (50+ fields), psychometric testing

## Build and Test
- Setup: `composer run setup` (installs dependencies, generates key, runs migrations/seeders)
- Development: `composer run dev` (starts server, queue worker, asset watcher)
- Testing: `php vendor/bin/pest` (Pest framework, not PHPUnit)
- Assets: `npm run build` for production compilation

## Conventions
- **UUID Routing**: All main routes use UUID parameters (pre-configured in `routes/web.php`)
- **Email Verification**: Standard Laravel signed verification links via `MustVerifyEmail`
- **Role Checks**: Use `auth`, `verified`, `role:hr|superadmin` middleware stack for admin endpoints
- **Enums**: Job model uses LEVELS/DIVISIONS constants for dropdowns
- **Seeders**: Run `RolesAndStagesSeeder` before `DemoDataSeeder` for proper data setup
- **Queue**: Database backend for jobs; ensure `jobs` table exists
- **Testing**: Use Pest DSL syntax for assertions (not traditional PHPUnit)</content>
<parameter name="filePath">c:\Users\raulm\Downloads\Human-HR\.github\copilot-instructions.md
