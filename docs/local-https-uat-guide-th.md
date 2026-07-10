# คู่มือ HTTPS สำหรับ UAT และการเตรียม Production

เอกสารนี้อธิบายการตั้งค่า HTTPS สำหรับทดสอบระบบ MSU-EVA ในเครื่อง รวมถึงสิ่งที่ต้องเปลี่ยนเมื่อจะนำระบบขึ้น Production จริง

## ภาพรวม

ระบบ Laravel ทำงานอยู่ที่:

```text
http://127.0.0.1:8000
```

URL นี้เป็น HTTP ธรรมดา จึงยังไม่ได้เข้ารหัสข้อมูลระหว่าง browser กับ server สำหรับการทดสอบ UAT จึงเพิ่ม HTTPS proxy ไว้ด้านหน้า:

```text
Browser
   ↓ HTTPS
https://msu-eva.test:8443
   ↓ HTTP ภายในเครื่อง
http://127.0.0.1:8000
   ↓
Laravel
```

ผู้ใช้งานเปิดระบบผ่าน `https://msu-eva.test:8443` ส่วน proxy จะรับคำขอ HTTPS แล้วส่งต่อไปยัง Laravel ที่พอร์ต 8000

## 1. mkcert และ Local CA คืออะไร

`mkcert` เป็นเครื่องมือสำหรับสร้าง certificate ที่ใช้ทดสอบ HTTPS ในเครื่อง development หรือ UAT

Local CA คือหน่วยออก certificate ที่สร้างและติดตั้งไว้เฉพาะในเครื่องนี้ ทำให้ Windows และ browser เชื่อถือ certificate ที่ `mkcert` สร้างขึ้น

สิ่งที่ติดตั้งไว้:

- โปรแกรม `mkcert`
- Local CA ใน Windows trust store
- Certificate สำหรับ `msu-eva.test`, `localhost`, `127.0.0.1` และ `::1`
- Private key สำหรับใช้เปิด HTTPS proxy

Certificate และ private key อยู่ใน:

```text
storage/uat-https/
```

โฟลเดอร์นี้ถูกเพิ่มใน `.gitignore` แล้ว จึงไม่ถูก commit เข้า Git

> ห้ามนำ private key หรือไฟล์ Local CA key ไปเผยแพร่ ส่งให้ผู้อื่น หรือใช้กับ Production

## 2. HTTPS proxy คืออะไร

ไฟล์ `scripts/local-https-proxy.mjs` เป็น reverse proxy ขนาดเล็กที่สร้างด้วย Node.js โดยทำหน้าที่:

1. เปิด HTTPS ที่ `https://msu-eva.test:8443`
2. ใช้ certificate จาก `storage/uat-https/`
3. รับ request จาก browser
4. ส่ง request ต่อไปยัง Laravel ที่ `http://127.0.0.1:8000`
5. ส่ง response จาก Laravel กลับไปยัง browser

Proxy จะส่ง header ต่อไปนี้ให้ Laravel เพื่อแจ้งว่า request ต้นทางเป็น HTTPS:

```text
X-Forwarded-Proto: https
X-Forwarded-Host: msu-eva.test:8443
X-Forwarded-Port: 8443
```

## 3. วิธีเปิดระบบผ่าน HTTPS

เพิ่มชื่อ local domain ในไฟล์ `C:\Windows\System32\drivers\etc\hosts` ด้วยสิทธิ์ Administrator:

```text
127.0.0.1 msu-eva.test
```

จากนั้นรัน `ipconfig /flushdns` หนึ่งครั้ง

เปิด terminal แรกแล้วรัน Laravel:

```powershell
php artisan serve
```

เปิด terminal ที่สองแล้วรัน HTTPS proxy:

```powershell
npm run https:uat
```

จากนั้นเปิด:

```text
https://msu-eva.test:8443/login
```

หาก proxy ทำงานอยู่แล้ว จะพบข้อความ:

```text
Local HTTPS proxy is already running at https://msu-eva.test:8443
```

ข้อความนี้ไม่ใช่ error หมายความว่าสามารถเปิด URL ดังกล่าวได้ทันที

## 4. ปัญหา EADDRINUSE คืออะไร

`EADDRINUSE` หมายถึงมีโปรแกรมกำลังใช้พอร์ต 8443 อยู่แล้ว สาเหตุที่พบคือ HTTPS proxy ตัวเดิมยังทำงานอยู่ แต่มีการสั่ง `npm run https:uat` ซ้ำ

เดิม Node.js จะแสดง stack trace และหยุดด้วย error ปัจจุบันแก้ให้ script ตรวจสอบ service ที่พอร์ต 8443 ก่อน หากพบว่าเป็น local HTTPS proxy ตัวเดิม จะแสดงข้อความว่า proxy ทำงานอยู่แล้วและจบด้วย exit code 0

## 5. เหตุผลที่โลโก้หายบน HTTPS

ก่อนแก้ไข หน้า login เปิดผ่าน HTTPS แต่ Laravel สร้าง URL โลโก้เป็น HTTP:

```text
http://msu-eva.test:8443/favicon-msu.png
```

Browser จึงบล็อกรูปในฐานะ mixed content เพราะหน้า HTTPS ไม่ควรโหลด resource ผ่าน HTTP

แก้ไขโดยตั้งค่าใน `bootstrap/app.php` ให้ Laravel เชื่อถือ reverse proxy จาก loopback address:

```php
$middleware->trustProxies(at: env('TRUSTED_PROXIES', '127.0.0.1,::1'));
```

หลังแก้ Laravel รับรู้ว่า request ต้นทางเป็น HTTPS และสร้าง URL โลโก้เป็น:

```text
https://msu-eva.test:8443/favicon-msu.png
```

ผลตรวจสอบคือไฟล์โลโก้ตอบ HTTP 200 และแสดงบนหน้า login ได้ตามปกติ

## 6. Regression test คืออะไร

Regression test คือ automated test ที่ช่วยป้องกันไม่ให้ปัญหาเดิมกลับมาอีก

เพิ่ม test ใน `tests/Feature/Auth/AuthenticationTest.php` เพื่อจำลอง request ที่เข้ามาผ่าน HTTPS proxy และตรวจว่า:

- หน้า login ตอบสำเร็จ
- URL โลโก้ใช้ `https://`
- ไม่มี URL โลโก้แบบ `http://msu-eva.test:8443`

ผลทดสอบ Auth/Login หลังแก้ไข:

```text
6 tests passed
16 assertions
```

## 7. ผล UAT

ผลการทดสอบ HTTPS สำหรับ UAT environment:

- URL: `https://msu-eva.test:8443/login`
- Application response: HTTP 200
- Certificate verification: `Verify return code: 0 (ok)`
- โลโก้: HTTP 200 และแสดงผลผ่าน HTTPS
- `UAT-SEC-001`: PASS สำหรับ UAT environment
- ผลรวม UAT: PASS 50/50

ผลนี้ยืนยันว่าระบบรองรับ HTTPS ในเครื่อง UAT แต่ยังไม่ใช่การยืนยัน Production เนื่องจากใช้ local domain และ local certificate

## 8. สิ่งที่ต้องเปลี่ยนก่อน Production

Production ห้ามใช้:

- `mkcert`
- Local CA
- `msu-eva.test:8443`
- `npm run https:uat`
- `scripts/local-https-proxy.mjs` เป็น public-facing proxy

ก่อนเปิดใช้งานจริง ต้องเตรียม:

1. Domain หรือ subdomain ของลูกค้า เช่น `eva.ph.msu.ac.th`
2. DNS ที่ชี้ domain ไปยัง Production server
3. Certificate จาก CA ที่ browser เชื่อถือ เช่น Let's Encrypt
4. Nginx, Apache หรือ Load Balancer สำหรับรับ HTTPS
5. การ redirect HTTP ไป HTTPS
6. การต่ออายุ certificate อัตโนมัติ

ตัวอย่างค่าที่ควรตั้งใน Production `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://eva.ph.msu.ac.th
SESSION_SECURE_COOKIE=true
TRUSTED_PROXIES=127.0.0.1,::1
```

หาก reverse proxy อยู่คนละเครื่องกับ Laravel หรือใช้ Cloudflare/Load Balancer ต้องเปลี่ยน `TRUSTED_PROXIES` เป็น IP หรือ CIDR ของ proxy จริง ห้ามกำหนดให้เชื่อถือทุก IP โดยไม่จำเป็น

หลังแก้ `.env` ให้รัน:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 9. Checklist ก่อน Production Go-live

- [ ] Domain ชี้ไปยัง Production server ถูกต้อง
- [ ] Certificate ออกให้ตรงกับ domain
- [ ] Browser ไม่แสดงคำเตือน certificate
- [ ] Certificate chain ถูกต้องและยังไม่หมดอายุ
- [ ] HTTP redirect ไป HTTPS
- [ ] หน้า login, โลโก้, CSS, JavaScript และรูปทั้งหมดโหลดผ่าน HTTPS
- [ ] Login และ Logout ทำงานผ่าน HTTPS
- [ ] Session cookie มี `Secure` และ `HttpOnly`
- [ ] ไม่พบ mixed content ใน browser console
- [ ] ทดสอบจากเครื่องและเครือข่ายอื่น
- [ ] ทดสอบ `UAT-SEC-001` ซ้ำด้วย domain ของลูกค้า

## 10. คำแนะนำเมื่อนำระบบขึ้น Production

การตั้งค่า `msu-eva.test` มีไว้ใช้เฉพาะเครื่อง UAT ไม่ต้องนำชื่อดังกล่าวหรือ Windows hosts file ไปตั้งใน Production

### สิ่งที่ควรขอจากลูกค้า

- Domain หรือ subdomain จริง เช่น `eva.ph.msu.ac.th`
- สิทธิ์หรือผู้รับผิดชอบสำหรับตั้งค่า DNS
- IP หรือชื่อเครื่อง Production server
- รูปแบบการติดตั้งว่าใช้ Nginx, Apache, Cloudflare หรือ Load Balancer
- ผู้รับผิดชอบ certificate และการต่ออายุ
- วันและเวลาสำหรับ Production go-live

### ลำดับการติดตั้งที่แนะนำ

1. ติดตั้ง Laravel และฐานข้อมูลบน Production server
2. ตั้งค่า `.env` สำหรับ Production โดยไม่คัดลอก `.env` จากเครื่อง UAT ทั้งไฟล์
3. ชี้ DNS ของ domain จริงไปยัง Production server
4. ติดตั้ง certificate จาก CA ที่ browser เชื่อถือ
5. ตั้ง Nginx/Apache หรือ Load Balancer ให้ส่ง request ไปยัง Laravel
6. ตั้ง `APP_URL` ให้ตรงกับ URL จริง
7. เปิดใช้ secure session cookie
8. ตั้ง trusted proxy ให้ตรงกับโครงสร้าง server
9. สร้าง Laravel production cache
10. ทดสอบระบบผ่าน domain จริงก่อนอนุญาตให้ผู้ใช้เข้าใช้งาน

ตัวอย่าง `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://eva.ph.msu.ac.th
SESSION_SECURE_COOKIE=true
TRUSTED_PROXIES=127.0.0.1,::1
```

หาก Cloudflare, Load Balancer หรือ reverse proxy อยู่คนละเครื่อง ต้องใช้ IP/CIDR ของ proxy จริงใน `TRUSTED_PROXIES` แทนค่า loopback

### สิ่งที่ไม่ต้องนำไปใช้ใน Production

- ไม่ต้องแก้ Windows hosts file
- ไม่ใช้ชื่อ `msu-eva.test`
- ไม่ใช้ certificate จาก `mkcert`
- ไม่ติดตั้ง Local CA บนเครื่องผู้ใช้
- ไม่รัน `npm run https:uat`
- ไม่ใช้ `scripts/local-https-proxy.mjs` เป็น Production reverse proxy
- ไม่ commit หรือคัดลอก private key จาก `storage/uat-https/`

### สิ่งที่เก็บไว้ใน codebase ได้

- การตั้งค่า `trustProxies` ใน `bootstrap/app.php`
- Regression test ที่ตรวจ URL ของ asset ผ่าน HTTPS
- คู่มือและรายงาน UAT
- Local HTTPS scripts สำหรับใช้ทดสอบในเครื่อง development/UAT ต่อไป

### การตรวจสอบหลัง Deploy

เปิด `https://<domain-จริง>` จากเครื่องอื่นและตรวจอย่างน้อยรายการต่อไปนี้:

- หน้า login เปิดได้โดย browser ไม่เตือน certificate
- โลโก้ รูป CSS และ JavaScript โหลดครบ
- ไม่มี mixed content ใน browser console
- Login, Logout และ Session ทำงานปกติ
- HTTP redirect ไป HTTPS
- Cookie สำคัญมี `Secure` และ `HttpOnly`
- Certificate ตรงกับ domain และยังไม่หมดอายุ
- ไม่เปิดเผย error หรือ debug information

หลังจากผ่านรายการเหล่านี้ ให้ทดสอบ `UAT-SEC-001` ซ้ำและบันทึกผลเป็น Production verification แยกจากผล UAT local

## 11. ไฟล์ที่เกี่ยวข้อง

- `docs/uat-test-result-2026-06-30.md` — รายงานผล UAT
- `scripts/local-https-proxy.mjs` — HTTPS proxy สำหรับ local UAT
- `package.json` — คำสั่ง `npm run https:uat`
- `bootstrap/app.php` — การตั้งค่า trusted proxy
- `tests/Feature/Auth/AuthenticationTest.php` — regression test สำหรับ HTTPS asset
- `.gitignore` — ป้องกัน certificate และ private key เข้า Git

## สรุปสั้น ๆ

การตั้งค่าปัจจุบันมีไว้พิสูจน์ว่าระบบทำงานผ่าน HTTPS ได้ใน UAT environment โดยไม่ต้องรอโดเมนลูกค้า เมื่อขึ้น Production ต้องใช้ domain, certificate และ reverse proxy จริง แล้วทดสอบ HTTPS ซ้ำก่อน Go-live
