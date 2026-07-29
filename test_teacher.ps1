$loginResp = Invoke-RestMethod -Uri "https://mmp.sital.info.np/api/auth/login" -Method POST -ContentType "application/json" -Body '{"email":"teacher@test.com","password":"password"}'
$token = $loginResp.data.token
$role = $loginResp.data.user.role
$name = $loginResp.data.user.name
Write-Host "=== LOGIN ==="
Write-Host "Name: $name | Role: $role | Token: $token"

$h = @{"Authorization"="Bearer $token";"Accept"="application/json"}
$base = "https://mmp.sital.info.np/api/v1/teacher"

Write-Host "`n=== DASHBOARD ==="
$dash = Invoke-RestMethod -Uri "$base/dashboard" -Method GET -Headers $h
Write-Host ($dash | ConvertTo-Json -Depth 6)

Write-Host "`n=== PROFILE ==="
$prof = Invoke-RestMethod -Uri "$base/profile" -Method GET -Headers $h
Write-Host ($prof | ConvertTo-Json -Depth 4)

Write-Host "`n=== TODAY SCHEDULE ==="
$sched = Invoke-RestMethod -Uri "$base/today-schedule" -Method GET -Headers $h
Write-Host ($sched | ConvertTo-Json -Depth 4)

Write-Host "`n=== TIMETABLE ==="
$tt = Invoke-RestMethod -Uri "$base/timetable" -Method GET -Headers $h
Write-Host "has_timetable=$($tt.data.has_timetable)"
$tt.data.timetable | ForEach-Object { Write-Host "$($_.day): $($_.classes.Count) classes" }
