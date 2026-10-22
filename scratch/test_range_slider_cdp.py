import subprocess, time, json, urllib.request, socket, struct, os, base64

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
    s.recv(4096)

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

        while True:
            header = s.recv(2)
            if not header: break
            b1, b2 = header[0], header[1]
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

def main():
    chrome_path = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
    port = 9225
    proc = subprocess.Popen([
        chrome_path,
        '--headless=new',
        f'--remote-debugging-port={port}',
        '--disable-gpu',
        '--window-size=1280,950',
        'http://localhost/drivesphere_updated/scratch/login_as_carlos.php'
    ])
    time.sleep(2)

    try:
        tabs = json.loads(urllib.request.urlopen(f'http://localhost:{port}/json').read().decode())
        ws_url = tabs[0]['webSocketDebuggerUrl']
        s, send_cmd = cdp_session(ws_url)

        # 1. Navigate to browse.php
        send_cmd('Page.navigate', {'url': 'http://localhost/drivesphere_updated/customer/browse.php'}, req_id=1)
        time.sleep(1.5)

        # 2. Open Filter modal
        res_open = send_cmd('Runtime.evaluate', {
            'expression': '''
                (function() {
                    const btn = document.getElementById('filterToggleBtn');
                    if (btn) btn.click();
                    return document.getElementById('filterModalBackdrop').classList.contains('open');
                })()
            '''
        }, req_id=2)
        print("Filter modal open state:", res_open['result']['result']['value'])
        time.sleep(0.5)

        # 3. Simulate dragging range sliders: min to 2000, max to 10000
        res_slide = send_cmd('Runtime.evaluate', {
            'expression': '''
                (function() {
                    const minS = document.getElementById('rangeMinSlider');
                    const maxS = document.getElementById('rangeMaxSlider');
                    const fill = document.getElementById('sliderFill');
                    const minSel = document.getElementById('filterMinPrice');
                    const maxSel = document.getElementById('filterMaxPrice');

                    minS.value = 2000;
                    minS.dispatchEvent(new Event('input', { bubbles: true }));

                    maxS.value = 10000;
                    maxS.dispatchEvent(new Event('input', { bubbles: true }));

                    return JSON.stringify({
                        minSlider: minS.value,
                        maxSlider: maxS.value,
                        minSelect: minSel.value,
                        maxSelect: maxSel.value,
                        minText: minSel.options[minSel.selectedIndex].text,
                        maxText: maxSel.options[maxSel.selectedIndex].text,
                        fillLeft: fill.style.left,
                        fillRight: fill.style.right
                    });
                })()
            '''
        }, req_id=3)
        slide_info = json.loads(res_slide['result']['result']['value'])
        print("\n--- TEST 1: Slider Drag Interaction ---")
        print("Slider Min:", slide_info['minSlider'], "-> Dropdown Min:", slide_info['minText'])
        print("Slider Max:", slide_info['maxSlider'], "-> Dropdown Max:", slide_info['maxText'])
        print("Fill Track Left:", slide_info['fillLeft'], "| Fill Track Right:", slide_info['fillRight'])

        # 4. Take Screenshot of Modal matching user uploaded image
        time.sleep(0.3)
        shot = send_cmd('Page.captureScreenshot', {'format': 'png'}, req_id=4)
        with open('scratch/modal_range_slider_shot.png', 'wb') as f:
            f.write(base64.b64decode(shot['result']['data']))
        print("Screenshot saved to scratch/modal_range_slider_shot.png")

        # 5. Test Dropdown -> Slider reverse sync
        res_reverse = send_cmd('Runtime.evaluate', {
            'expression': '''
                (function() {
                    const minS = document.getElementById('rangeMinSlider');
                    const maxS = document.getElementById('rangeMaxSlider');
                    const fill = document.getElementById('sliderFill');
                    const minSel = document.getElementById('filterMinPrice');
                    const maxSel = document.getElementById('filterMaxPrice');

                    minSel.value = 3500;
                    minSel.dispatchEvent(new Event('change', { bubbles: true }));

                    maxSel.value = 8000;
                    maxSel.dispatchEvent(new Event('change', { bubbles: true }));

                    return JSON.stringify({
                        minSlider: minS.value,
                        maxSlider: maxS.value,
                        fillLeft: fill.style.left,
                        fillRight: fill.style.right
                    });
                })()
            '''
        }, req_id=5)
        rev_info = json.loads(res_reverse['result']['result']['value'])
        print("\n--- TEST 2: Dropdown to Slider Reverse Sync ---")
        print("Dropdown 3500 -> Slider Min:", rev_info['minSlider'], "| Left:", rev_info['fillLeft'])
        print("Dropdown 8000 -> Slider Max:", rev_info['maxSlider'], "| Right:", rev_info['fillRight'])

        s.close()
    finally:
        proc.kill()

if __name__ == '__main__':
    main()
