import subprocess
import time
import urllib.request
import json
import base64
import os

url = "http://localhost/drivesphere_updated/customer/vehicle.php?id=12"
cmd = [
    r'C:\Program Files\Google\Chrome\Application\chrome.exe',
    '--headless=new',
    '--disable-gpu',
    '--window-size=1280,800',
    f'--screenshot={os.path.abspath("scratch/vehicle_details_no_wishlist.png")}',
    url
]
proc = subprocess.run(cmd, capture_output=True, text=True, timeout=15)
print("Return code:", proc.returncode)
if os.path.exists("scratch/vehicle_details_no_wishlist.png"):
    print("Screenshot captured successfully! Size:", os.path.getsize("scratch/vehicle_details_no_wishlist.png"))
else:
    print("Screenshot failed.")
