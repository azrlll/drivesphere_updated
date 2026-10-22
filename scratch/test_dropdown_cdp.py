import subprocess, time, json, urllib.request, socket, struct, os, base64

chrome_path = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
proc = subprocess.Popen([
    chrome_path,
    '--headless',
    '--remote-debugging-port=9222',
    '--disable-gpu',
    '--window-size=1280,1000',
    'http://localhost/drivesphere_updated/index.php'
])
time.sleep(2)

def cdp_session(ws_url):
    url_part = ws_url.replace('ws://', '')
    host_port, path = url_part.split('/', 1)
    host, port = host_port.split(':')
    port = int(port)
    path = '/' + path

    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.connect((host, port))
    
    key = base64.b64encode(os.urandom(16)).decode('utf-8')
    handshake = (
        f'GET {path} HTTP/1.1\r\n'
        f'Host: {host}:{port}\r\n'
        f'Upgrade: websocket\r\n'
        f'Connection: Upgrade\r\n'
        f'Sec-WebSocket-Key: {key}\r\n'
        f'Sec-WebSocket-Version: 13\r\n\r\n'
    )
    s.sendall(handshake.encode())
    res = s.recv(4096)

    def send_cmd(method, params=None, req_id=1):
        msg = json.dumps({'id': req_id, 'method': method, 'params': params or {}})
        payload = msg.encode()
        frame = bytearray([0x81])
        length = len(payload)
        mask = os.urandom(4)
        if length <= 125:
            frame.append(0x80 | length)
        elif length <= 65535:
            frame.append(0x80 | 126)
            frame.extend(struct.pack('!H', length))
        else:
            frame.append(0x80 | 127)
            frame.extend(struct.pack('!Q', length))
        frame.extend(mask)
        masked_payload = bytearray(payload[i] ^ mask[i % 4] for i in range(length))
        frame.extend(masked_payload)
        s.sendall(frame)

        # Read frames until we get response for req_id
        while True:
            header = s.recv(2)
            if not header: break
            b1, b2 = header[0], header[1]
            opcode = b1 & 0x0F
            masked = b2 & 0x80
            payload_len = b2 & 0x7F
            if payload_len == 126:
                payload_len = struct.unpack('!H', s.recv(2))[0]
            elif payload_len == 127:
                payload_len = struct.unpack('!Q', s.recv(8))[0]
            
            data = bytearray()
            while len(data) < payload_len:
                chunk = s.recv(min(payload_len - len(data), 65536))
                if not chunk: break
                data.extend(chunk)
            
            try:
                obj = json.loads(data.decode('utf-8', errors='ignore'))
                if obj.get('id') == req_id:
                    return obj
            except:
                pass
        return None

    return s, send_cmd

try:
    tabs = json.loads(urllib.request.urlopen('http://localhost:9222/json').read().decode())
    ws_url = None
    for t in tabs:
        if 'DriveSphere' in t.get('title', ''):
            ws_url = t['webSocketDebuggerUrl']
            break
            
    s, send_cmd = cdp_session(ws_url)
    print('Clicking #accountTrigger...')
    res = send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("accountTrigger").click();'}, req_id=10)
    print('Clicked. Waiting for CSS animation...')
    time.sleep(0.4)
    
    # Check classes
    status = send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("accountDropdown").className + " | " + document.getElementById("accountTrigger").className'}, req_id=11)
    print('State:', status['result']['result']['value'])
    
    shot = send_cmd('Page.captureScreenshot', {'format': 'png'}, req_id=12)
    b64 = shot['result']['data']
    with open(r'c:\xampp\htdocs\drivesphere_updated\scratch\account_dropdown_settled.png', 'wb') as f:
        f.write(base64.b64decode(b64))
    print('Screenshot successfully written: account_dropdown_settled.png')
    s.close()
finally:
    proc.kill()
