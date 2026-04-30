# Test Update User dengan PowerShell/Curl
# File: test_update_user_curl.ps1

$TOKEN = "your_token_here"
$USER_ID = "2"
$BASE_URL = "http://localhost:8000"

Write-Host "=== Testing Update User Endpoint ===" -ForegroundColor Green
Write-Host ""

# Test 1: Update tanpa file (hanya text fields)
Write-Host "Test 1: Update user tanpa file upload" -ForegroundColor Yellow

$headers = @{
    "Authorization" = "Bearer $TOKEN"
    "Accept" = "application/json"
}

$form = @{
    "name" = "Administrator Updated"
    "email" = "admin_updated@test.com"
    "username" = "admin_updated"
}

$response = Invoke-WebRequest -Uri "$BASE_URL/api/auth/user/$USER_ID" `
    -Method PUT `
    -Headers $headers `
    -Form $form `
    -ContentType "multipart/form-data" `
    -Verbose

Write-Host "Response Status: $($response.StatusCode)"
Write-Host "Response Body:"
$response.Content | ConvertFrom-Json | ConvertTo-Json

Write-Host "`n`nTest 2: Update user dengan file upload" -ForegroundColor Yellow

# Test 2: Update dengan file upload
# Note: Sesuaikan path file gambar
$imagePath = "C:\path\to\image.jpg"

if (Test-Path $imagePath) {
    $form2 = @{
        "name" = "Administrator With Photo"
        "email" = "admin_photo@test.com"
        "username" = "admin_photo"
        "profile_photo" = Get-Item -Path $imagePath
    }

    $response2 = Invoke-WebRequest -Uri "$BASE_URL/api/auth/user/$USER_ID" `
        -Method PUT `
        -Headers $headers `
        -Form $form2 `
        -ContentType "multipart/form-data" `
        -Verbose

    Write-Host "Response Status: $($response2.StatusCode)"
    Write-Host "Response Body:"
    $response2.Content | ConvertFrom-Json | ConvertTo-Json
} else {
    Write-Host "Image not found at $imagePath" -ForegroundColor Red
}

Write-Host "`nTests complete!" -ForegroundColor Green
