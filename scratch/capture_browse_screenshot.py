import subprocess
import os

url = "http://localhost/drivesphere_updated/customer/browse.php"
cmd = [
    r'C:\Program Files\Google\Chrome\Application\chrome.exe',
    '--headless=new',
    '--disable-gpu',
    '--window-size=1280,800',
    f'--screenshot={os.path.abspath("scratch/browse_no_wishlist.png")}',
    url
]
subprocess.run(cmd, capture_output=True, text=True, timeout=15)
if os.path.exists("scratch/browse_no_wishlist.png"):
    print("Browse screenshot captured successfully!")
