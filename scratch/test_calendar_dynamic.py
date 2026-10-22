import subprocess, time, json, urllib.request, socket, struct, os, base64

chrome_path = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
port = 9555
user_dir = r'C:\Users\KEVIN CHRISTOPHER\.gemini\antigravity\brain\3620498d-2354-4568-8b3d-43b698ae2dd2\scratch\chrome_cal_profile'
proc = subprocess.Popen([
    chrome_path,
    '--headless=new',
    f'--remote-debugging-port={port}',
    f'--user-data-dir={user_dir}',
    '--disable-gpu',
    '--window-size=1024,960',
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

        data_buf = bytearray()
        s.settimeout(3.0)
        while True:
            try:
                chunk = s.recv(65536)
                if not chunk: break
                data_buf.extend(chunk)
                needle = f'"id":{r_id}'.encode()
                needle_space = f'"id": {r_id}'.encode()
                if needle in data_buf or needle_space in data_buf:
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

    # 1. Verify Up-to-date initial state
    r = send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("lblCalendarMonth").textContent.trim()'})
    month_text = r.get('result', {}).get('result', {}).get('value', '')
    print(f"Initial calendar month: {month_text}")
    assert month_text == 'October 2026', f"Expected October 2026, got {month_text}"

    r = send_cmd('Runtime.evaluate', {'expression': 'document.querySelector(".adm-cal-cell.is-active-solid").getAttribute("data-day")'})
    active_day = r.get('result', {}).get('result', {}).get('value', '')
    print(f"Active today day: {active_day}")
    assert active_day == '22', f"Expected today 22, got {active_day}"

    r = send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("calPickupsCount").textContent.trim()'})
    pickups = r.get('result', {}).get('result', {}).get('value', '')
    print(f"Pickups text: {pickups}")
    assert 'Pickups Today' in pickups

    # Take screenshot of up-to-date initial dashboard
    res = send_cmd('Page.captureScreenshot')
    if 'result' in res and 'data' in res['result']:
        with open('scratch/calendar_uptodate.png', 'wb') as f:
            f.write(base64.b64decode(res['result']['data']))
        print("Saved scratch/calendar_uptodate.png")

    # 2. Test clicking Previous Month button (check previous month & previous dates)
    send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("btnCalPrevMonth").click()'})
    time.sleep(0.4)
    r = send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("lblCalendarMonth").textContent.trim()'})
    prev_month_text = r.get('result', {}).get('result', {}).get('value', '')
    print(f"Previous month after clicking prev arrow: {prev_month_text}")
    assert prev_month_text == 'September 2026', f"Expected September 2026, got {prev_month_text}"

    # Verify Today button is shown
    r = send_cmd('Runtime.evaluate', {'expression': 'window.getComputedStyle(document.getElementById("btnJumpToday")).display'})
    jump_display = r.get('result', {}).get('result', {}).get('value', '')
    print(f"Jump to Today button display: {jump_display}")
    assert jump_display != 'none'

    # 3. Test clicking a previous date in September (e.g. Day 10)
    send_cmd('Runtime.evaluate', {'expression': 'document.querySelector(".adm-cal-cell[data-day=\\"10\\"]").click()'})
    time.sleep(0.3)
    r = send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("calPickupsCount").textContent.trim()'})
    p_sep10 = r.get('result', {}).get('result', {}).get('value', '')
    print(f"Day 10 September dispatch stats: {p_sep10}")
    assert 'Sep 10' in p_sep10, f"Expected Sep 10 in stats, got {p_sep10}"

    # Take screenshot of previous date inspection view
    res = send_cmd('Page.captureScreenshot')
    if 'result' in res and 'data' in res['result']:
        with open('scratch/calendar_previous_date.png', 'wb') as f:
            f.write(base64.b64decode(res['result']['data']))
        print("Saved scratch/calendar_previous_date.png")

    # 4. Test clicking Jump to Today
    send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("btnJumpToday").click()'})
    time.sleep(0.4)
    r = send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("lblCalendarMonth").textContent.trim()'})
    back_month = r.get('result', {}).get('result', {}).get('value', '')
    print(f"Month after Jump to Today: {back_month}")
    assert back_month == 'October 2026'

    # 5. Test clicking Month Dropdown Pill
    send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("btnMonthToggle").click()'})
    time.sleep(0.4)
    r = send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("monthDropdownMenu").classList.contains("is-open")'})
    is_open = r.get('result', {}).get('result', {}).get('value', False)
    print(f"Month dropdown is open: {is_open}")
    assert is_open is True

    # Take screenshot of open month dropdown
    res = send_cmd('Page.captureScreenshot')
    if 'result' in res and 'data' in res['result']:
        with open('scratch/calendar_month_dropdown_open.png', 'wb') as f:
            f.write(base64.b64decode(res['result']['data']))
        print("Saved scratch/calendar_month_dropdown_open.png")

    # 6. Test selecting April 2025 from dropdown (the mockup month)
    send_cmd('Runtime.evaluate', {'expression': 'Array.from(document.querySelectorAll(".adm-month-opt")).find(el => el.textContent.includes("April 2025")).click()'})
    time.sleep(0.4)
    r = send_cmd('Runtime.evaluate', {'expression': 'document.getElementById("lblCalendarMonth").textContent.trim()'})
    apr_text = r.get('result', {}).get('result', {}).get('value', '')
    print(f"Selected month from dropdown: {apr_text}")
    assert apr_text == 'April 2025'

    print("\nALL DYNAMIC CALENDAR & PREVIOUS DATE TESTS PASSED 100%!")

finally:
    proc.terminate()
