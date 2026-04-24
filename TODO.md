# Remove Application Features - TODO

- [x] Plan approved by user
- [ ] Edit `app/Http/Controllers/Admin/DashboardController.php`
  - [ ] Remove `use App\Models\Application;`
  - [ ] Remove application count variables and usages
  - [ ] Remove application KPI card
  - [ ] Remove `countApplications()` method
  - [ ] Remove application alert from `buildAlerts()`
  - [ ] Remove application-related payload data
  - [ ] Remove `recentApplications` from `buildDashboardState()`
- [ ] Edit `app/Http/Controllers/Admin/SettingsController.php`
  - [ ] Remove `email_new_applications` validation rule
  - [ ] Remove `email_new_applications` from save call
- [ ] Edit `app/Services/NotificationPreferenceService.php`
  - [ ] Remove `email_new_applications` from principal defaults
- [ ] Edit `resources/views/admin/settings/index.blade.php`
  - [ ] Remove "New Applications" email toggle UI
- [ ] Edit `resources/views/hod/settings/index.blade.php`
  - [ ] Remove "New Applications" email toggle UI
- [ ] Clear Laravel cache and verify

