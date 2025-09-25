# การอัปเดตระบบจัดการ Roles ในรอบการประเมิน

## สิ่งที่เปลี่ยนแปลง

### 1. การเพิ่ม Auto Role Assignment
เมื่อสร้างหรือแก้ไขรอบการประเมิน ระบบจะทำการกำหนด roles ให้ผู้ใช้โดยอัตโนมัติ:
- **ผู้ใช้ในตำแหน่งผู้ประเมิน** จะได้รับ role "ผู้ประเมิน"
- **ผู้ใช้ในตำแหน่งผู้รับการประเมิน** จะได้รับ role "ผู้รับการประเมิน"

### 2. การป้องกัน Role Conflicts
- เมื่อกำหนด role ใหม่ ระบบจะลบ role ที่ขัดแย้งออกก่อน
- ผู้ประเมิน: จะถูกลบ role "ผู้รับการประเมิน" (ถ้ามี)
- ผู้รับการประเมิน: จะถูกลบ role "ผู้ประเมิน" (ถ้ามี)

### 3. ฟังก์ชันใหม่ใน AssignmentDataController

#### `assignUserRoles($users, $newRole, $removeRole = null)`
- จัดการการกำหนด roles อย่างปลอดภัย
- รองรับทั้ง Collection และ HasMany Relation
- ตรวจสอบว่ามี role อยู่แล้วหรือไม่ก่อนเพิ่ม

#### `hasOtherAssignments($user, $currentAssignmentDataId = null)`
- ตรวจสอบว่าผู้ใช้มี assignments อื่นอยู่หรือไม่
- ใช้สำหรับการตัดสินใจลบ role

## วิธีการใช้งาน

### สำหรับผู้พัฒนา

1. **การสร้างรอบการประเมินใหม่**
   ```php
   // เมื่อเรียกใช้ store method
   POST /assignment-data
   {
       "evaluators": 1,    // position_id
       "evaluatees": 2,    // position_id  
       "report_data_id": 1,
       "start_time": "2025-01-01",
       "end_time": "2025-01-31"
   }
   
   // ผู้ใช้ในตำแหน่ง 1 จะได้ role "ผู้ประเมิน"
   // ผู้ใช้ในตำแหน่ง 2 จะได้ role "ผู้รับการประเมิน"
   ```

2. **การแก้ไขรอบการประเมิน**
   ```php
   // เมื่อเรียกใช้ update method
   PUT /assignment-data/{id}
   // roles จะถูกอัปเดตตามตำแหน่งใหม่
   ```

### สำหรับผู้ใช้งาน

1. **การสร้างรอบการประเมิน**
   - เลือกตำแหน่งผู้ประเมินและผู้รับการประเมิน
   - กำหนดช่วงเวลาและเกณฑ์การประเมิน
   - บันทึก → ระบบจะกำหนด roles โดยอัตโนมัติ

2. **การตรวจสอบ roles**
   - ผู้ใช้สามารถเข้าไปดู roles ของตนเองในหน้าโปรไฟล์
   - ผู้ดูแลระบบสามารถจัดการ roles ได้ในหน้า User Management

## คำเตือนและข้อควรระวัง

### 1. การลบ Assignment
- การลบ assignment **ไม่** ลบ roles โดยอัตโนมัติ
- เหตุผล: ผู้ใช้อาจมี assignments อื่นอยู่
- ควรตรวจสอบและจัดการ roles ด้วยตนเองหากจำเป็น

### 2. Roles ที่จำเป็น
ก่อนใช้งาน ตรวจสอบว่ามี roles ต่อไปนี้ในฐานข้อมูล:
- `ผู้ประเมิน`
- `ผู้รับการประเมิน`

### 3. การ Rollback
หากต้องการยกเลิกการกำหนด roles:
```php
// สามารถใช้ Artisan command หรือ Tinker
User::find($userId)->removeRole('ผู้ประเมิน');
User::find($userId)->assignRole('ผู้รับการประเมิน');
```

## การทดสอบ

### 1. ทดสอบการสร้าง Assignment
```bash
# สร้าง assignment ใหม่
POST /assignment-data

# ตรวจสอบ roles ของผู้ใช้
SELECT * FROM model_has_roles WHERE model_type = 'App\\Models\\User';
```

### 2. ทดสอบการแก้ไข Assignment
```bash
# แก้ไขตำแหน่งในรอบการประเมิน
PUT /assignment-data/{id}

# ตรวจสอบว่า roles ถูกอัปเดต
```

## ข้อมูลเพิ่มเติม

- ระบบ Role ใช้ Spatie Laravel-Permission package
- ความสัมพันธ์: Position hasMany Users
- User ใช้ HasRoles trait
- การกำหนด roles เป็น transactional (มี rollback)

## การแก้ไขปัญหา

### ปัญหา: ไม่พบ roles
```
Error: ไม่พบ role: ผู้ประเมิน
```
**วิธีแก้:** รัน RoleSeeder หรือสร้าง roles ด้วยตนเอง

### ปัญหา: ผู้ใช้ไม่ได้ role
**ตรวจสอบ:**
1. ผู้ใช้มีอยู่ในตำแหน่งที่เลือกหรือไม่
2. Roles มีอยู่ในฐานข้อมูลหรือไม่
3. Transaction มี error หรือไม่

### ปัญหา: Role ซ้อนทับกัน
**วิธีแก้:** ระบบจะจัดการให้อัตโนมัติ โดยลบ role ที่ขัดแย้งก่อนเพิ่ม role ใหม่
