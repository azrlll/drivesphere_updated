import subprocess
import os

url = "http://localhost/drivesphere_updated/policies.php"
cmd = [
    r'C:\Program Files\Google\Chrome\Application\chrome.exe',
    '--headless=new',
    '--disable-gpu',
    '--window-size=1280,1000',
    f'--screenshot={os.path.abspath("scratch/footer_close_up.png")}',
    url
]
subprocess.run(cmd, capture_output=True, text=True, timeout=15)

# Or scroll to footer on index
from urllib.request import urlopen
import json, base64, socket, time
cmd2 = [r'C:\Program Files\Google\Chrome\Application\chrome.exe', '--headless=new', '--remote-debugging-port=9223', '--disable-gpu', url]
p = subprocess.Popen(cmd2)
time.sleep(2)
try:
    tabs = json.loads(urlopen('http://localhost:9223/json').read().decode())
    ws_url = tabs[0]['webSocketDebuggerUrl']
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    parts = ws_url.replace('ws://', '').split('/')
    host, port = parts[0].split(':')
    path = '/' + '/'.join(parts[1:])
    s.connect((host, int(port)))
    key = base64.b64encode(b'key1234567890123').decode().strip()
    req = f'GET {path} HTTP/1.1\r\nHost: {host}:{port}\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Key: {key}\r\nSec-WebSocket-Version: 13\r\n\r\n'
    s.sendall(req.encode())
    s.recv(4096)
    
    # Scroll to footer
    eval_payload = json.dumps({'id': 1, 'method': 'Runtime.evaluate', 'params': {'expression': 'window.scrollTo(0, document.body.scrollHeight);'}}).encode('utf-8')
    header = bytearray([0x81, 0x80 | len(eval_payload)]) + b'\x12\x34\x56\x78' + bytearray([b ^ b'\x12\x34\x56\x78'[i % 4] for i, b in enumerate(eval_payload)])
    s.sendall(header)
    time.sleep(0.5)
    
    # Screenshot
    shot_payload = json.dumps({'id': 2, 'method': 'Page.captureScreenshot', 'params': {'format': 'png'}}).encode('utf-8')
    header2 = bytearray([0x81, 0x80 | len(shot_payload)]) + b'\x12\x34\x56\x78' + bytearray([b ^ b'\x12\x34\x56\x78'[i % 4] for i, b in enumerate(shot_payload)])
    s.sendall(header2)
    
    data = b''
    while True:
        chunk = s.recv(65536)
        if not chunk: break
        data += chunk
        if b'"result":{"data":' in data:
            break
            
    time.sleep(0.5)
    while True:
        try:
            s.settimeout(0.5)
            chunk = s.recv(65536)
            if not chunk: break
            data += chunk
        except:
            break
            
    import re
    m = re.search(rb'"data":"([^"]+)"', data)
    if m:
        with open(r'c:\xampp\htdocs\drivesphere_updated\scratch\footer_scrolled.png', 'wb') as f:
            f.write(base64.b64decode(m.group(1).decode()))
        print("Scrolled footer captured!")
    s.close()
finally:
    p.kill()
