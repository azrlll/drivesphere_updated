import subprocess, os

dest = os.path.abspath("scratch/test_modal_slider.png")
if os.path.exists(dest):
    os.remove(dest)

cmd = [
    r"C:\Program Files\Google\Chrome\Application\chrome.exe",
    "--headless=new",
    "--disable-gpu",
    "--window-size=1280,950",
    f"--screenshot={dest}",
    "http://localhost/drivesphere_updated/scratch/browse_with_modal_open.php"
]
res = subprocess.run(cmd, capture_output=True, text=True, timeout=15)
print("Return code:", res.returncode)
print("File exists:", os.path.exists(dest))
if os.path.exists(dest):
    print("File size:", os.path.getsize(dest))
