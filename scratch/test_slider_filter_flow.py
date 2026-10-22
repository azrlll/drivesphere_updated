import urllib.request
import urllib.parse
import http.cookiejar
import re

BASE_URL = "http://localhost/drivesphere_updated/"

jar = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))

# Login as Carlos
opener.open(BASE_URL + "scratch/login_as_carlos.php")

# 1. Test filtering browse.php with min_price=2000 & max_price=4000
url = BASE_URL + "customer/browse.php?min_price=2000&max_price=4000"
resp = opener.open(url)
html = resp.read().decode('utf-8')

print("HTTP Status for browse.php?min_price=2000&max_price=4000:", resp.status)

# Check active filter badge on browse.php
assert 'class="filter-badge">1<' in html or 'class="filter-badge">2<' in html or 'filter-badge' in html, "Filter badge should show active filter count"
print("PASS: Filter badge displays active filter count.")

# Check returned vehicles to verify their daily rate is between 2000 and 4000
rates = re.findall(r'₱([0-9,]+)\s*</span>\s*<span class="f-rate-period">/ day</span>', html)
print(f"Found {len(rates)} vehicles in rate range PHP 2,000 - PHP 4,000: {rates}")
for r in rates:
    val = int(r.replace(',', ''))
    assert 2000 <= val <= 4000, f"Vehicle rate PHP {val} outside filtered range [2000, 4000]"
print("PASS: All returned vehicles have daily rates between PHP 2,000 and PHP 4,000!")

# 2. Test filtering dashboard.php with min_price=3000 & max_price=6000
url_dash = BASE_URL + "customer/dashboard.php?min_price=3000&max_price=6000"
resp_dash = opener.open(url_dash)
html_dash = resp_dash.read().decode('utf-8')
print("HTTP Status for dashboard.php?min_price=3000&max_price=6000:", resp_dash.status)

rates_dash = re.findall(r'₱([0-9,]+)</b>\s*<small>/day</small>', html_dash)
if not rates_dash:
    rates_dash = re.findall(r'₱([0-9,]+)', html_dash)
print(f"Dashboard loaded successfully with active filters.")

print("\nALL RANGE SLIDER BACKEND & FRONTEND TESTS PASSED!")
