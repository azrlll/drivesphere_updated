import subprocess
import os

url = "http://localhost/drivesphere_updated/customer/browse.php"
cmd = [
    r'C:\Program Files\Google\Chrome\Application\chrome.exe',
    '--headless=new',
    '--disable-gpu',
    '--window-size=1280,1400',
    f'--screenshot={os.path.abspath("scratch/browse_cards_view.png")}',
    url
]
subprocess.run(cmd, capture_output=True, text=True, timeout=15)
if os.path.exists("scratch/browse_cards_view.png"):
    print("Cards view screenshot captured!")
