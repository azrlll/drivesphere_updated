import urllib.request
import urllib.parse
import http.cookiejar

cookie_jar = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cookie_jar))

# Step 1: Login as admin
login_url = "http://localhost/drivesphere_updated/scratch/login_as_admin.php"
req = urllib.request.Request(login_url)
resp = opener.open(req)
print(f"Logged in, landed on: {resp.url} (Code: {resp.status})")

# Read dashboard HTML
dashboard_html = resp.read().decode('utf-8')

# Tests of all clickable links
tests = [
    ("Card 1 (Fortuner)", "admin/manage.php?q=Fortuner", "Fortuner"),
    ("Card 2 (Montero)", "admin/manage.php?q=Montero", "Montero"),
    ("Card 3 (Civic)", "admin/manage.php?q=Civic", "Civic"),
    ("Card 4 (HiAce)", "admin/manage.php?q=HiAce", "HiAce"),
    ("Card 5 (Everest)", "admin/manage.php?q=Everest", "Everest"),
    ("View All Tasks", "admin/requests.php", "Customer Rental Requests"),
    ("Approve Now", "admin/requests.php?tab=pending", "DS-8921"),
    ("Review Claim", "admin/damage.php?id=21", "Rear bumper scuff"),
    ("Validate KYC", "admin/customers.php?q=Maria", "Maria Santos"),
    ("Total Units badge", "admin/manage.php", "Manage Vehicles"),
    ("Donut chart link", "admin/manage.php", "Manage Vehicles"),
    ("Legend Rented", "admin/manage.php?status=rented", "Rented"),
    ("Legend Available", "admin/manage.php?status=available", "Available"),
    ("Legend Maintenance", "admin/manage.php?status=maintenance", "Maintenance"),
    ("Legend Reserved", "admin/rentals.php?tab=reserved", "Rentals"),
    ("Calendar Pickups", "admin/rentals.php?tab=reserved", "Rentals"),
    ("Calendar Returns", "admin/rentals.php?tab=en_route", "Rentals"),
]

base_url = "http://localhost/drivesphere_updated/"
passed = 0
for name, path, expected_text in tests:
    target = urllib.parse.urljoin(base_url, path)
    r = opener.open(target)
    body = r.read().decode('utf-8')
    if expected_text in body:
        print(f"PASS: {name} -> {path} (found '{expected_text}')")
        passed += 1
    else:
        print(f"FAIL: {name} -> {path} (did NOT find '{expected_text}')")

print(f"\nAll tests completed: {passed}/{len(tests)} passed!")
