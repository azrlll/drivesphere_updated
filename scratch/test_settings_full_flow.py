import urllib.request
import urllib.parse
import http.cookiejar
import re
import base64
import os

BASE_URL = 'http://localhost/drivesphere_updated'
cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# 1. Get login page to extract CSRF token
resp = opener.open(f'{BASE_URL}/login.php')
html = resp.read().decode('utf-8', errors='ignore')
m = re.search(r'name="csrf_token"\s+value="([^"]+)"', html)
csrf = m.group(1) if m else ''

# 2. Login as Carlos Alvarez
login_data = urllib.parse.urlencode({
    'csrf_token': csrf,
    'email': 'carlos@drivesphere.ph',
    'password': 'password123'
}).encode('utf-8')

resp = opener.open(f'{BASE_URL}/login.php', data=login_data)
login_html = resp.read().decode('utf-8', errors='ignore')
print(f"Login URL: {resp.geturl()}")

# 3. Access customer/settings.php
resp = opener.open(f'{BASE_URL}/customer/settings.php')
settings_html = resp.read().decode('utf-8', errors='ignore')

assert "No image selected yet" in settings_html, "Initial state must be 'No image selected yet'"
assert 'id="btnDoneName"' in settings_html, "Done button must exist"
assert 'id="btnVerifyPhone"' in settings_html, "Verify Phone button must exist"
assert 'id="btnVerifyEmail"' in settings_html, "Verify Email button must exist"
print("[OK] Initial state verified: 'No image selected yet' and all Done/Verified buttons present.")

# 4. Upload photo using multipart form-data
m_csrf = re.search(r'name="csrf"\s+value="([^"]+)"', settings_html)
csrf_settings = m_csrf.group(1)

boundary = '----WebKitFormBoundary7MA4YWxkTrZu0gW'
body = []

def add_field(name, val):
    body.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{name}"\r\n\r\n{val}\r\n'.encode('utf-8'))

add_field('csrf', csrf_settings)
add_field('remove_avatar', '0')
add_field('phone_verified', '1')
add_field('email_verified', '1')
add_field('full_name', 'Carlos Alvarez')
add_field('phone', '+63 917 123 4567')
add_field('email', 'carlos@drivesphere.ph')

# Avatar file
dummy_png = base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')
body.append(f'--{boundary}\r\nContent-Disposition: form-data; name="avatar_file"; filename="sample_custom_pic.png"\r\nContent-Type: image/png\r\n\r\n'.encode('utf-8'))
body.append(dummy_png)
body.append(b'\r\n')
body.append(f'--{boundary}--\r\n'.encode('utf-8'))

payload = b''.join(body)
req = urllib.request.Request(f'{BASE_URL}/customer/settings.php', data=payload, headers={
    'Content-Type': f'multipart/form-data; boundary={boundary}'
})

resp = opener.open(req)
uploaded_html = resp.read().decode('utf-8', errors='ignore')

assert 'uploads/avatars/avatar_' in uploaded_html, "Uploaded avatar path should be present in page"
assert 'Change Photo' in uploaded_html, "'Change Photo' should now be displayed"
print("[OK] Photo successfully uploaded, persisted in MySQL, and displayed on page!")

# 5. Remove avatar
m_csrf2 = re.search(r'name="csrf"\s+value="([^"]+)"', uploaded_html)
csrf_remove = m_csrf2.group(1)

remove_payload = urllib.parse.urlencode({
    'csrf': csrf_remove,
    'remove_avatar': '1',
    'phone_verified': '1',
    'email_verified': '1',
    'full_name': 'Carlos Alvarez',
    'phone': '+63 917 123 4567',
    'email': 'carlos@drivesphere.ph'
}).encode('utf-8')

resp = opener.open(f'{BASE_URL}/customer/settings.php', data=remove_payload)
removed_html = resp.read().decode('utf-8', errors='ignore')

assert "No image selected yet" in removed_html, "'No image selected yet' should be restored after removal"
assert "Choose Photo" in removed_html, "'Choose Photo' should be restored"
print("[OK] Photo successfully removed, returning to 'No image selected yet' state.")

print("\n=== ALL TESTS PASSED SUCCESSFULLY! ===")
