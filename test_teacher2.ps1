$loginResp = Invoke-RestMethod -Uri "https://mmp.sital.info.np/api/auth/login" -Method POST -ContentType "application/json" -Body '{"email":"teacher@test.com","password":"password"}'
$token = $loginResp.data.token
Write-Host "Token: $token | Role: $($loginResp.data.user.role)"

$h = @{"Authorization"="Bearer $token";"Accept"="application/json"}
$base = "https://mmp.sital.info.np/api/v1/teacher"

Write-Host "`n=== DASHBOARD ==="
try {
  $d = Invoke-RestMethod -Uri "$base/dashboard" -Method GET -Headers $h
  Write-Host "success=$($d.success)"
  Write-Host "teacher_name=$($d.data.teacher_name)"
  Write-Host "total_classes=$($d.data.total_classes)"
  Write-Host "total_students=$($d.data.total_students)"
  Write-Host "pending_marks=$($d.data.pending_marks)"
  Write-Host "pending_assignments=$($d.data.pending_assignments)"
} catch {
  $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
  Write-Host "ERR: $($reader.ReadToEnd())"
}

Write-Host "`n=== TODAY SCHEDULE ==="
try {
  $s = Invoke-RestMethod -Uri "$base/today-schedule" -Method GET -Headers $h
  Write-Host "success=$($s.success) today=$($s.data.today) day=$($s.data.day)"
  Write-Host "classes count=$($s.data.classes.Count)"
  $s.data.classes | ForEach-Object {
    Write-Host "  $($_.start_time)-$($_.end_time) | $($_.subject) | $($_.program) Sem$($_.semester) | $($_.room)"
  }
} catch {
  $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
  Write-Host "ERR: $($reader.ReadToEnd())"
}

Write-Host "`n=== PROFILE ==="
try {
  $p = Invoke-RestMethod -Uri "$base/profile" -Method GET -Headers $h
  Write-Host "name=$($p.data.name) dept=$($p.data.department) emp_id=$($p.data.employee_id)"
} catch {
  $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
  Write-Host "ERR: $($reader.ReadToEnd())"
}
