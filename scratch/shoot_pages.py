import urllib.request
import urllib.parse
import http.cookiejar
import subprocess
import re
import os

BASE_URL = 'http://localhost/drivesphere_updated'
CHROME_PATH = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
SCRATCH_DIR = r'c:\xampp\htdocs\drivesphere_updated\scratch'

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# 1. Login as customer
login_page = opener.open(f'{BASE_URL}/login.php').read().decode('utf-8')
m_csrf = re.search(r'name="csrf"\s+value="([^"]+)"', login_page)
csrf = m_csrf.group(1) if m_csrf else ''

post_data = urllib.parse.urlencode({
    'csrf': csrf,
    'email': 'carlos@drivesphere.ph',
    'password': 'password123'
}).encode('utf-8')
opener.open(f'{BASE_URL}/login.php', data=post_data)

pages = [
    ('contact.php', 'shot_public_contact.png', 1300),
    ('customer/contact.php', 'shot_customer_contact.png', 1300),
    ('policies.php', 'shot_public_policies.png', 1400),
    ('customer/policies.php', 'shot_customer_policies.png', 1400),
    ('about.php', 'shot_public_about.png', 1600),
    ('customer/about.php', 'shot_customer_about.png', 1600),
    ('fleet.php', 'shot_fleet.png', 1400),
]

for url_rel, out_img, height in pages:
    html = opener.open(f'{BASE_URL}/{url_rel}').read().decode('utf-8')
    base_href = f'{BASE_URL}/{os.path.dirname(url_rel)}/' if '/' in url_rel else f'{BASE_URL}/'
    html = html.replace('<head>', f'<head><base href="{base_href}">')
    
    html_file = os.path.join(SCRATCH_DIR, f'temp_{out_img}.html')
    with open(html_file, 'w', encoding='utf-8') as f:
        f.write(html)
        
    out_path = os.path.join(SCRATCH_DIR, out_img)
    cmd = [
        CHROME_PATH,
        '--headless',
        '--disable-gpu',
        f'--window-size=1280,{height}',
        f'--screenshot={out_path}',
        f'file:///{html_file.replace(os.sep, "/")}'
    ]
    subprocess.run(cmd, check=True)
    print(f'Captured {out_img}')

print('All screenshots captured successfully!')
