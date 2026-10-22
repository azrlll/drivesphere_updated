import re
import urllib.request
import urllib.parse
import http.cookiejar

BASE_URL = "http://localhost/drivesphere_updated/"

def create_session():
    jar = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    return opener

def extract_links(html, current_url):
    links = []
    # match href="..."
    for match in re.finditer(r'href=["\']([^"\'#]+?)(?:#[^"\']*)?["\']', html, re.IGNORECASE):
        href = match.group(1).strip()
        if href.startswith(('javascript:', 'mailto:', 'tel:', 'data:')):
            continue
        # Skip logout links in crawler so sessions stay active
        if 'logout' in href.lower():
            continue
        full_url = urllib.parse.urljoin(current_url, href)
        if full_url.startswith(BASE_URL):
            links.append(full_url)
    return list(set(links))

def extract_forms(html, current_url):
    forms = []
    for match in re.finditer(r'<form\b[^>]*action=["\']([^"\']+)["\']', html, re.IGNORECASE):
        action = match.group(1).strip()
        full_url = urllib.parse.urljoin(current_url, action)
        if full_url.startswith(BASE_URL):
            forms.append(full_url)
    return list(set(forms))

def test_url(opener, url):
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 LinkAuditor'})
        resp = opener.open(req)
        return resp.status, resp.url, resp.read().decode('utf-8', errors='replace')
    except urllib.error.HTTPError as e:
        return e.code, url, ""
    except Exception as e:
        return 0, url, str(e)

def main():
    print("=" * 70)
    print("COMPREHENSIVE LINK & NAVIGATION AUDIT: PUBLIC, CUSTOMER & ADMIN")
    print("=" * 70)
    
    total_checked = 0
    failures = []
    
    # =========================================================================
    # 1. GUEST / PUBLIC SUITE
    # =========================================================================
    print("\n--- 1. Testing Public & Guest Pages ---")
    guest_opener = create_session()
    
    public_entry_pages = [
        "index.php",
        "rental.php",
        "fleet.php",
        "about.php",
        "help.php",
        "policies.php",
        "contact.php",
        "book.php?id=1",
        "login.php",
        "register.php",
        "admin/login.php",
        "admin/register.php"
    ]
    
    public_links_to_verify = set()
    for p in public_entry_pages:
        url = urllib.parse.urljoin(BASE_URL, p)
        status, landed_url, body = test_url(guest_opener, url)
        total_checked += 1
        if status != 200:
            print(f"  [FAIL] {p} -> HTTP {status}")
            failures.append((p, status, "Expected 200"))
        else:
            print(f"  [OK] {p} -> HTTP 200")
            for link in extract_links(body, landed_url):
                public_links_to_verify.add(link)
                
    print(f"\n  Checking {len(public_links_to_verify)} unique links found in public pages...")
    for link in public_links_to_verify:
        status, landed_url, _ = test_url(guest_opener, link)
        total_checked += 1
        rel = link.replace(BASE_URL, "")
        if status not in (200, 302):
            print(f"    [FAIL] Link {rel} -> HTTP {status}")
            failures.append((rel, status, "Public Link Error"))
            
    # Verify index.php vehicle links for guest route to login.php?redirect=...
    _, _, index_body = test_url(guest_opener, BASE_URL + "index.php")
    assert "login.php?redirect=" in index_body, "Guest index.php vehicle cards must route via login.php"
    print("  [OK] Verified guest card links on index.php route through login.php")
    
    # =========================================================================
    # 2. CUSTOMER SUITE
    # =========================================================================
    print("\n--- 2. Testing Customer Portal Pages & Link Flow ---")
    cust_opener = create_session()
    login_status, landed, _ = test_url(cust_opener, BASE_URL + "scratch/login_as_carlos.php")
    assert login_status == 200, f"Customer login failed: HTTP {login_status}"
    print(f"  [OK] Logged in as customer Carlos, landed on {landed}")
    
    customer_pages = [
        "customer/dashboard.php",
        "customer/browse.php",
        "customer/vehicle.php?id=1",
        "customer/request.php?vehicle_id=1",
        "customer/history.php",
        "customer/receipt.php?ref=DS-RN-2026-0001",
        "customer/return_record.php?ref=DS-RN-2026-0003",
        "customer/charges.php?ref=DS-RN-2026-0003",
        "customer/settings.php",
        "customer/notifications.php",
        "customer/about.php",
        "customer/help.php",
        "customer/policies.php",
        "customer/contact.php"
    ]
    
    customer_links_to_verify = set()
    for p in customer_pages:
        url = urllib.parse.urljoin(BASE_URL, p)
        status, landed_url, body = test_url(cust_opener, url)
        total_checked += 1
        if status != 200:
            print(f"  [FAIL] {p} -> HTTP {status}")
            failures.append((p, status, "Expected 200"))
        else:
            print(f"  [OK] {p} -> HTTP 200")
            for link in extract_links(body, landed_url):
                customer_links_to_verify.add(link)
                
    print(f"\n  Checking {len(customer_links_to_verify)} unique links found in customer pages...")
    for link in customer_links_to_verify:
        status, landed_url, _ = test_url(cust_opener, link)
        total_checked += 1
        rel = link.replace(BASE_URL, "")
        if status not in (200, 302):
            print(f"    [FAIL] Link {rel} -> HTTP {status}")
            failures.append((rel, status, "Customer Link Error"))
            
    # Verify logged-in customer on index.php and rental.php has DIRECT vehicle links without login
    status, _, cust_index = test_url(cust_opener, BASE_URL + "index.php")
    assert "customer/vehicle.php?id=" in cust_index, "Customer on index.php must have direct customer/vehicle.php links"
    assert "login.php?redirect=" not in cust_index, "Customer on index.php must NOT have login.php redirect links"
    print("  [OK] Verified customer vehicle cards on index.php link directly to customer/vehicle.php")
    
    status, _, cust_rental = test_url(cust_opener, BASE_URL + "rental.php")
    assert "customer/vehicle.php?id=" in cust_rental, "Customer on rental.php must have direct customer/vehicle.php links"
    assert "login.php?redirect=" not in cust_rental, "Customer on rental.php must NOT have login.php redirect links"
    print("  [OK] Verified customer vehicle cards on rental.php link directly to customer/vehicle.php")

    # Verify book.php?id=1 redirects directly to customer/vehicle.php?id=1
    status, book_landed, _ = test_url(cust_opener, BASE_URL + "book.php?id=1")
    assert "customer/vehicle.php" in book_landed, f"book.php did not direct customer to vehicle details: {book_landed}"
    print(f"  [OK] book.php?id=1 directed logged-in customer directly to {book_landed}")

    # =========================================================================
    # 3. ADMIN SUITE
    # =========================================================================
    print("\n--- 3. Testing Admin Portal Pages & Link Flow ---")
    admin_opener = create_session()
    login_status, landed, _ = test_url(admin_opener, BASE_URL + "scratch/login_as_admin.php")
    assert login_status == 200, f"Admin login failed: HTTP {login_status}"
    print(f"  [OK] Logged in as administrator, landed on {landed}")
    
    admin_pages = [
        "admin/dashboard.php",
        "admin/manage.php",
        "admin/manage.php?status=available",
        "admin/manage.php?status=rented",
        "admin/manage.php?status=maintenance",
        "admin/maintenance.php",
        "admin/vehicle_form.php?id=1",
        "admin/vehicle_form.php",
        "admin/merchant.php",
        "admin/rentals.php",
        "admin/rentals.php?tab=en_route",
        "admin/rentals.php?tab=reserved",
        "admin/rentals.php?tab=returned",
        "admin/rentals.php?tab=all",
        "admin/requests.php",
        "admin/requests.php?tab=pending",
        "admin/requests.php?tab=reserved",
        "admin/customers.php",
        "admin/damage.php?id=21",
        "admin/receipt.php?id=21",
        "admin/record_return.php?id=21",
        "admin/settings.php",
        "admin/notifications.php",
        "admin/help.php"
    ]
    
    admin_links_to_verify = set()
    for p in admin_pages:
        url = urllib.parse.urljoin(BASE_URL, p)
        status, landed_url, body = test_url(admin_opener, url)
        total_checked += 1
        if status != 200:
            print(f"  [FAIL] {p} -> HTTP {status}")
            failures.append((p, status, "Expected 200"))
        else:
            print(f"  [OK] {p} -> HTTP 200")
            for link in extract_links(body, landed_url):
                admin_links_to_verify.add(link)
                
    print(f"\n  Checking {len(admin_links_to_verify)} unique links found in admin pages...")
    for link in admin_links_to_verify:
        status, landed_url, _ = test_url(admin_opener, link)
        total_checked += 1
        rel = link.replace(BASE_URL, "")
        if status not in (200, 302):
            print(f"    [FAIL] Link {rel} -> HTTP {status}")
            failures.append((rel, status, "Admin Link Error"))
            
    # Verify admin footer links
    status, _, adm_dash_body = test_url(admin_opener, BASE_URL + "admin/dashboard.php")
    assert "admin/manage.php" in adm_dash_body, "Admin footer must link to admin/manage.php"
    assert "admin/help.php" in adm_dash_body, "Admin footer must link to admin/help.php"
    assert "admin/notifications.php" in adm_dash_body, "Admin footer must link to admin/notifications.php"
    print("  [OK] Verified admin footer links stay in admin context")
    
    # =========================================================================
    # SUMMARY
    # =========================================================================
    print("\n" + "=" * 70)
    print(f"TOTAL URLS / LINKS CHECKED: {total_checked}")
    if failures:
        print(f"FAILURES FOUND: {len(failures)}")
        for f in failures:
            print(f"  - {f[0]}: {f[1]} ({f[2]})")
    else:
        print("ALL AUDITS PASSED WITH ZERO BROKEN LINKS AND ZERO MISMATCHES! (100% OK)")
    print("=" * 70)

if __name__ == '__main__':
    main()
