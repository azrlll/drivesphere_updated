import urllib.request
import urllib.parse
import http.cookiejar
import re

BASE_URL = 'http://localhost/drivesphere_updated'

print("--- TEST 1: Guest clicking Rent Now on Homepage ---")
# Use clean cookie jar without session
cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# Visit book.php?vehicle=1 as guest
resp = opener.open(f'{BASE_URL}/book.php?vehicle=1')
final_url = resp.geturl()
print(f"Final URL for book.php?vehicle=1: {final_url}")
assert "login.php" in final_url, "Guest clicking Rent Now must be redirected to login.php"
assert "redirect=" in final_url and "customer%2Fvehicle.php" in final_url or "customer/vehicle.php" in final_url, "Redirect param must point to customer/vehicle.php"
print("[OK] Guest on homepage Rent Now correctly redirected to login page!")

print("\n--- TEST 2: Guest clicking Rent Now and View Details on Rental Page ---")
# On rental.php, check links for vehicle cards
rental_resp = opener.open(f'{BASE_URL}/rental.php')
rental_html = rental_resp.read().decode('utf-8', errors='ignore')

# Check that Rent Now and View Details links point to book.php
assert 'href="book.php?vehicle=' in rental_html, "Rental page cards must link to book.php"
# Find a sample link
m = re.search(r'href="(book\.php\?vehicle=\d+[^"]*)"', rental_html)
sample_link = m.group(1)
print(f"Sample vehicle link on rental.php: {sample_link}")

# Open sample link as guest
click_resp = opener.open(f'{BASE_URL}/{sample_link}')
print(f"Final URL when clicking vehicle on rental.php: {click_resp.geturl()}")
assert "login.php" in click_resp.geturl(), "Clicking Rent Now / View Details on rental.php must redirect to login.php"
print("[OK] Guest on rental.php Rent Now / View Details correctly redirected to login page!")

print("\n--- TEST 3: Logging in and redirecting to selected vehicle ---")
# Extract CSRF token from login page
login_html = click_resp.read().decode('utf-8', errors='ignore')
m_csrf = re.search(r'name="csrf"\s+value="([^"]+)"', login_html)
csrf = m_csrf.group(1) if m_csrf else ''

# Extract redirect target
m_red = re.search(r'name="redirect"\s+value="([^"]+)"', login_html)
if not m_red:
    # check query param
    from urllib.parse import urlparse, parse_qs
    parsed = urlparse(click_resp.geturl())
    redirect_target = parse_qs(parsed.query).get('redirect', ['customer/dashboard.php'])[0]
else:
    redirect_target = m_red.group(1)

login_data = urllib.parse.urlencode({
    'csrf': csrf,
    'email': 'carlos@drivesphere.ph',
    'password': 'password123'
}).encode('utf-8')

# Login request
post_req = urllib.request.Request(f'{BASE_URL}/login.php?redirect=' + urllib.parse.quote(redirect_target), data=login_data)
post_resp = opener.open(post_req)
print(f"Final URL after login: {post_resp.geturl()}")
assert "customer/vehicle.php" in post_resp.geturl(), "After login, must land on customer/vehicle.php"
print("[OK] Successfully logged in and redirected directly to vehicle details page!")

print("\n--- TEST 4: Logged-in Customer clicking Rent Now / View Details ---")
# Now with the logged-in session, visiting book.php?vehicle=5 should go directly to customer/vehicle.php?id=5
auth_resp = opener.open(f'{BASE_URL}/book.php?vehicle=5')
print(f"Logged-in user book.php?vehicle=5 destination: {auth_resp.geturl()}")
assert "customer/vehicle.php?id=5" in auth_resp.geturl(), "Logged in user must go directly to vehicle"
print("[OK] Logged-in customer accesses vehicle details directly without re-login!")

print("\n--- TEST 5: Daily Budget Range Filtering ---")
# 1. Test budget <= 2000
resp_budget_2000 = opener.open(f'{BASE_URL}/rental.php?max_price=2000')
html_2000 = resp_budget_2000.read().decode('utf-8', errors='ignore')
assert "Up to ₱2,000 / day" in html_2000, "Budget label must reflect ₱2,000"
assert "Toyota Fortuner" not in html_2000, "Fortuner (₱4,200) must NOT be shown for budget ₱2,000"
assert "Toyota HiAce" not in html_2000, "HiAce (₱4,800) must NOT be shown for budget ₱2,000"
print("[OK] Budget PHP 2,000 correctly filters out higher-priced cars!")

# 2. Test budget = 8500 (full inventory)
resp_budget_8500 = opener.open(f'{BASE_URL}/rental.php?max_price=8500')
html_8500 = resp_budget_8500.read().decode('utf-8', errors='ignore')
assert "30 verified vehicles" in html_8500, "Full inventory should show 30 vehicles"
print("[OK] Budget PHP 8,500 includes all fleet vehicles!")

print("\n--- TEST 6: Grid View Toggle ---")
resp_grid = opener.open(f'{BASE_URL}/rental.php?view=grid')
html_grid = resp_grid.read().decode('utf-8', errors='ignore')
assert 'class="vehicle-list grid-view"' in html_grid, "Container must have grid-view class when view=grid"
assert 'id="btnGridView" title="Grid View"' in html_grid, "Grid View button must exist"
print("[OK] Grid view successfully renders vehicle cards in grid layout!")

print("\n=== ALL USER REQUIREMENTS VERIFIED AND PASSED! ===")
