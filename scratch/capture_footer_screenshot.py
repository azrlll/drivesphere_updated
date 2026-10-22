import subprocess
import os

# 1. Public page footer screenshot
url1 = "http://localhost/drivesphere_updated/index.php"
cmd1 = [
    r'C:\Program Files\Google\Chrome\Application\chrome.exe',
    '--headless=new',
    '--disable-gpu',
    '--window-size=1280,1800',
    f'--screenshot={os.path.abspath("scratch/public_footer_clean.png")}',
    url1
]
subprocess.run(cmd1, capture_output=True, text=True, timeout=15)

# 2. Customer page footer screenshot
url2 = "http://localhost/drivesphere_updated/customer/dashboard.php"
cmd2 = [
    r'C:\Program Files\Google\Chrome\Application\chrome.exe',
    '--headless=new',
    '--disable-gpu',
    '--window-size=1280,1800',
    f'--screenshot={os.path.abspath("scratch/customer_footer_clean.png")}',
    url2
]
subprocess.run(cmd2, capture_output=True, text=True, timeout=15)

print("Screenshots captured successfully!")
