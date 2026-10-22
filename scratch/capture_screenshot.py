import subprocess
import time
import urllib.request
import json
import base64
import socket
import re

cmd = [r'C:\Program Files\Google\Chrome\Application\chrome.exe', '--headless=new', '--remote-debugging-port=9222', '--disable-gpu', 'http://localhost/drivesphere_updated/customer/settings.php']
proc = subprocess.Popen(cmd)
time.sleep(2)
try:
    tabs = json.loads(urllib.request.urlopen('http://localhost:9222/json').read().decode())
    ws_url = tabs[0]['webSocketDebuggerUrl']
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    parts = ws_url.replace('ws://', '').split('/')
    host, port = parts[0].split(':')
    path = '/' + '/'.join(parts[1:])
    s.connect((host, int(port)))
    key = base64.b64encode(b'settings_key_1234').decode().strip()
    req = f'GET {path} HTTP/1.1\r\nHost: {host}:{port}\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Key: {key}\r\nSec-WebSocket-Version: 13\r\n\r\n'
    s.sendall(req.encode())
    s.recv(4096)
    
    # Take screenshot
    payload = json.dumps({'id': 1, 'method': 'Page.captureScreenshot', 'params': {'format': 'png'}}).encode('utf-8')
    header = bytearray([0x81])
    length = len(payload)
    if length <= 125:
        header.append(0x80 | length)
    elif length <= 65535:
        header.append(0x80 | 126)
        header.extend(length.to_bytes(2, 'big'))
    mask = b'\x12\x34\x56\x78'
    header.extend(mask)
    masked = bytearray([b ^ mask[i % 4] for i, b in enumerate(payload)])
    s.sendall(header + masked)
    
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
            
    m = re.search(rb'"data":"([^"]+)"', data)
    if m:
        b64_str = m.group(1).decode()
        with open(r'c:\xampp\htdocs\drivesphere_updated\scratch\settings_final_verified.png', 'wb') as f:
            f.write(base64.b64decode(b64_str))
        print('Screenshot saved successfully to scratch/settings_final_verified.png')
    s.close()
finally:
    proc.kill()
