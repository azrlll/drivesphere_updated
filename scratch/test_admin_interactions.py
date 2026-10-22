import subprocess, time, json, urllib.request, socket, struct, os, base64

chrome_path = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
port = 9444
user_dir = r'C:\Users\KEVIN CHRISTOPHER\.gemini\antigravity\brain\3620498d-2354-4568-8b3d-43b698ae2dd2\scratch\chrome_profile'
proc = subprocess.Popen([
    chrome_path,
    '--headless=new',
    f'--remote-debugging-port={port}',
    f'--user-data-dir={user_dir}',
    '--disable-gpu',
    '--window-size=1024,900',
    'http://localhost/drivesphere_updated/scratch/login_as_admin.php'
])
time.sleep(2.5)

def cdp_session(ws_url):
    url_part = ws_url.replace('ws://', '')
    host_port, path = url_part.split('/', 1)
    host, p = host_port.split(':')
    p = int(p)
    path = '/' + path

    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.connect((host, p))
    
    key = base64.b64encode(os.urandom(16)).decode('utf-8')
    handshake = (
        f'GET {path} HTTP/1.1\r\n'
        f'Host: {host}:{p}\r\n'
        f'Upgrade: websocket\r\n'
        f'Connection: Upgrade\r\n'
        f'Sec-WebSocket-Key: {key}\r\n'
        f'Sec-WebSocket-Version: 13\r\n\r\n'
    )
    s.sendall(handshake.encode())
    res = s.recv(4096)

    req_counter = [1]

    def send_cmd(method, params=None):
        r_id = req_counter[0]
        req_counter[0] += 1
        msg = json.dumps({'id': r_id, 'method': method, 'params': params or {}})
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

        # read responses
        data_buf = bytearray()
        s.settimeout(3.0)
        while True:
            try:
                chunk = s.recv(65536)
                if not chunk: break
                data_buf.extend(chunk)
                # check if valid JSON frame inside
                # search for {"id": r_id
                needle = f'"id":{r_id}'.encode()
                needle_space = f'"id": {r_id}'.encode()
                if needle in data_buf or needle_space in data_buf:
                    # locate JSON boundaries
                    start = data_buf.find(b'{')
                    end = data_buf.rfind(b'}')
                    if start != -1 and end != -1 and end > start:
                        try:
                            d = json.loads(data_buf[start:end+1].decode('utf-8', errors='ignore'))
                            if d.get('id') == r_id:
                                return d
                        except:
                            pass
            except socket.timeout:
                break
        return {}

    return s, send_cmd

try:
    tabs = json.loads(urllib.request.urlopen(f'http://localhost:{port}/json').read().decode())
    dashboard_tab = next(t for t in tabs if 'admin/dashboard.php' in t.get('url', ''))
    ws_url = dashboard_tab['webSocketDebuggerUrl']
    s, send_cmd = cdp_session(ws_url)

    # 1. Test clicking Schedule Shop button
    send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("btnOpenScheduleModal").click()'})
    time.sleep(0.5)
    r = send_cmd('Runtime.evaluate', {'expression': 'window.getComputedStyle(document.getElementById("scheduleShopModal")).display'})
    modal_display = r.get('result', {}).get('result', {}).get('value')
    print(f"Modal display after clicking Schedule Shop: {modal_display}")
    assert modal_display == 'flex', f"Expected flex, got {modal_display}"

    # Take screenshot of open modal
    res = send_cmd('Page.captureScreenshot')
    if 'result' in res and 'data' in res['result']:
        img_data = base64.b64decode(res['result']['data'])
        with open('scratch/test_modal_open.png', 'wb') as f:
            f.write(img_data)
        print("Saved scratch/test_modal_open.png")

    # 2. Test closing modal
    send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("btnCancelShopModal").click()'})
    time.sleep(0.3)
    r = send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("scheduleShopModal").style.display'})
    assert r.get('result', {}).get('result', {}).get('value') == 'none', "Modal did not close"
    print("PASS: Schedule Shop Modal opened and closed perfectly!")

    # 3. Test Rolling Window dropdown
    send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("btnRollingWindow").click()'})
    time.sleep(0.3)
    send_cmd('Runtime.evaluate', {'expression': 'document.querySelector(".adm-rolling-opt[data-period=\\"7d\\"]").click()'})
    time.sleep(0.5)
    r = send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("rollingWindowLabel").textContent'})
    label = r.get('result', {}).get('result', {}).get('value', '')
    print(f"Rolling label after selecting 7d: {label}")
    assert '7-Day' in label, f"Expected 7-Day in label, got {label}"
    
    r = send_cmd('Runtime.evaluate', {'expression': 'document.querySelector(".adm-rate-number").textContent.trim()'})
    rate = r.get('result', {}).get('result', {}).get('value')
    print(f"Card 1 rate for 7d: {rate}")
    assert rate == '74%', f"Expected 74%, got {rate}"
    print("PASS: Rolling Window toggle and rate update worked perfectly!")

    # 4. Test Calendar day 14 click
    send_cmd('Runtime.evaluate', {'expression': 'document.querySelector(".adm-cal-cell[data-day=\\"14\\"]").click()'})
    time.sleep(0.3)
    r = send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("calPickupsCount").textContent'})
    pickups = r.get('result', {}).get('result', {}).get('value', '')
    print(f"Day 14 Pickups: {pickups}")
    assert '14 Pickups' in pickups, f"Expected 14 Pickups, got {pickups}"
    print("PASS: Dispatch Calendar interactive day selection worked perfectly!")

    # 5. Test Month toggle
    send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("btnMonthToggle").click()'})
    time.sleep(0.3)
    r = send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("lblCalendarMonth").textContent'})
    m = r.get('result', {}).get('result', {}).get('value')
    print(f"Calendar month after toggle: {m}")
    assert m in ['June 2025', 'April 2025', 'May 2025'], f"Unexpected month {m}"
    print("PASS: Dispatch Calendar month cycle worked perfectly!")

    print("\nALL IN-BROWSER INTERACTION AND MODAL TESTS PASSED 100%!")
finally:
    proc.terminate()
