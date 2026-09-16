# Collaboration Guidelines

Panduan kolaborasi untuk tim development Project Reservasi Fasilitas Kampus.

## Git Workflow

### Branch Strategy

```
main (protected)
├── feature/reservation-system
├── feature/report-system
├── feature/admin-dashboard
└── bugfix/validation-issue
```

**Branch Naming Convention:**
- `feature/nama-fitur` - untuk fitur baru
- `bugfix/nama-bug` - untuk bug fix
- `hotfix/nama-urgent` - untuk urgent production fix
- `docs/nama-doc` - untuk dokumentasi

### Development Flow

1. **Pull latest main**
   ```bash
   git checkout main
   git pull origin main
   ```

2. **Create feature branch**
   ```bash
   git checkout -b feature/nama-fitur
   ```

3. **Work & Commit regularly**
   ```bash
   git add .
   git commit -m "feat: implement reservation validation"
   ```

4. **Push to remote**
   ```bash
   git push origin feature/nama-fitur
   ```

5. **Create Pull Request**
   - Gunakan template PR
   - Request review dari minimal 1 team member
   - Wait for approval before merge

### Commit Message Format

Format: `<type>: <description>`

**Types:**
- `feat` - fitur baru
- `fix` - bug fix
- `docs` - dokumentasi
- `style` - formatting, missing semicolons, etc
- `refactor` - code refactoring
- `test` - menambah test
- `chore` - maintenance tasks

**Examples:**
```bash
git commit -m "feat: add facility availability checker"
git commit -m "fix: resolve slot conflict validation"
git commit -m "docs: update API documentation"
git commit -m "refactor: extract reservation service"
```

### Pull Request Guidelines

**PR Title Format:**
```
[FEATURE] Add reservation conflict checker
[BUGFIX] Fix date validation in report form
[DOCS] Update installation guide
```

**PR Description Template:**
```markdown
## What
Brief description of changes

## Why
Reason for this change

## How
Technical implementation details

## Testing
- [ ] Manual testing done
- [ ] Unit tests added/updated
- [ ] No console errors
- [ ] Works on Windows

## Screenshots (if UI changes)
[Add screenshots here]

## Related Issue
Closes #123
```

**Before Creating PR:**
- [ ] Code runs without errors
- [ ] Run `./vendor/bin/pint` (linter)
- [ ] Run `php artisan test`
- [ ] Update documentation if needed
- [ ] Remove debug code & console.log

### Code Review Checklist

**For Reviewer:**
- [ ] Code follows Laravel conventions
- [ ] No security vulnerabilities
- [ ] Error handling implemented
- [ ] Database queries optimized
- [ ] Validation rules complete
- [ ] Comments for complex logic
- [ ] No hardcoded values
- [ ] Responsive UI (if frontend)

## Coding Standards

### PHP/Laravel
- Follow PSR-12 standard
- Use type hints for parameters & return types
- Use Form Requests for validation
- Extract complex logic to Services
- Use Enums for status/role constants

### Blade/Frontend
- Use Tailwind utility classes
- Keep components small & reusable
- Validate forms on client & server side
- Handle loading & error states

### Database
- Use migrations for schema changes
- Never edit old migrations (create new ones)
- Add indexes for foreign keys & search columns
- Use soft deletes where appropriate

## Development Workflow

### Daily Routine
1. Start dengan pull latest main
2. Check Trello/Issues untuk task
3. Create/switch to feature branch
4. Code & test locally
5. Commit dengan clear message
6. Push & create PR (jika selesai)
7. Review PR dari team member

### Before Commit
```bash
# Format code
./vendor/bin/pint

# Run tests
php artisan test

# Check for errors
php artisan config:clear
php artisan route:clear
```

### Sync dengan Team
```bash
# Update local main
git checkout main
git pull origin main

# Update feature branch
git checkout feature/nama-fitur
git merge main

# Resolve conflicts if any
git status
```

## Communication

### Daily Standup (Async di WhatsApp Group)
**Format:**
- Yesterday: Apa yang dikerjakan kemarin
- Today: Apa yang akan dikerjakan hari ini
- Blockers: Ada kendala?

### Issue Reporting
**Format:**
```markdown
**Title:** [BUG] Reservation form validation error

**Description:**
Clear description of the issue

**Steps to Reproduce:**
1. Go to...
2. Click on...
3. See error

**Expected:** What should happen
**Actual:** What actually happens
**Environment:** Windows 11, PHP 8.5, MySQL 8.0
```

## Team Responsibilities

Setiap anggota team harus:
- Commit minimal 3x per week
- Review PR team member lain
- Update Trello card progress
- Hadir di meeting/standup
- Ask for help jika stuck >2 jam

## Conflict Resolution

Jika ada merge conflict:
1. Jangan panic
2. Komunikasi dengan team member yang conflict
3. Koordinasi siapa yang resolve
4. Test setelah resolve
5. Commit hasil resolution

## Resources

- Laravel Docs: https://laravel.com/docs/13.x
- Tailwind Docs: https://tailwindcss.com/docs
- Team Trello: [Link here]
- GitHub Repo: [Link here]

## Questions?

Jika ada pertanyaan tentang workflow atau standards, diskusikan di WhatsApp group atau create issue dengan label `question`.
