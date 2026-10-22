import subprocess
import os

chrome = r'C:\Program Files\Google\Chrome\Application\chrome.exe'

# 1. Grid view screenshot
cmd1 = [
    chrome, '--headless=new', '--disable-gpu', '--window-size=1280,1100',
    f'--screenshot={os.path.abspath("scratch/rental_grid_view.png")}',
    'http://localhost/drivesphere_updated/rental.php?view=grid'
]
subprocess.run(cmd1, capture_output=True, timeout=15)

# 2. Budget slider set to 2000
cmd2 = [
    chrome, '--headless=new', '--disable-gpu', '--window-size=1280,1000',
    f'--screenshot={os.path.abspath("scratch/rental_budget_2000.png")}',
    'http://localhost/drivesphere_updated/rental.php?max_price=2000'
]
subprocess.run(cmd2, capture_output=True, timeout=15)

print("Screenshots captured successfully!")
