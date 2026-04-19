# Fix SQLite GROUP_CONCAT ORDER BY Error in TeacherController

## Steps
- [x] 1. Edit `app/Http/Controllers/Admin/TeacherController.php`: Remove ORDER BY from GROUP_CONCAT raw SQL and add sort() in PHP map.
- [x] 2. Clear Laravel caches: `php artisan cache:clear && php artisan view:clear`
- [ ] 3. Test: Visit `/admin/teachers` - no SQL error, semester badges sorted.
- [ ] 4. Test pagination/filters: Semesters display correctly.
- [ ] 5. Verify blade rendering: Table/card views show sorted semesters.
- [ ] 6. Complete: Run `attempt_completion`.
