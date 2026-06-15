# Testing Report - EVA Pass 2

วันที่ทดสอบ: 11 มิถุนายน 2026  
ผู้ทดสอบ: Codex  
ระบบ: EVA Pass 2  
สภาพแวดล้อม: Local development workspace  

## Dashboard Summary

| Metric | Value |
| --- | ---: |
| Automated test suites | 24 |
| Automated test cases | 131 |
| Assertions | 440 |
| Passed test cases | 131 |
| Failed test cases | 0 |
| Pass rate | 100% |
| Frontend production build | Pass |
| Open defects | 0 |
| Test execution attempts | 2 |
| Failed execution attempts | 0 |

## Test Round Summary

| Round | Command / Scope | Test Count | Assertion Count | Failed Test Count | Failed Round Count | Result |
| --- | --- | ---: | ---: | ---: | ---: | --- |
| 1 | `composer test` - Automated regression | 131 | 440 | 0 | 0 | Pass |
| 2 | `npm run build` - Frontend production build | N/A | N/A | 0 | 0 | Pass |
| Total | Automated test + build verification | 131 | 440 | 0 | 0 | Pass |

## Test Execution Log

Template sheet reference: `Test_Execution_Log`

| TC ID | Description | Test Type | Actual Result | Status | Remarks |
| --- | --- | --- | --- | --- | --- |
| AT-001 | รัน automated test ทั้งหมดด้วย `composer test` | Automated / Regression | 131 tests, 440 assertions ผ่านทั้งหมด | Pass | ทดสอบ 1 รอบ, ไม่ผ่าน 0 รอบ, ครอบคลุม auth, dashboard, evaluation, report, settings, user |
| AT-002 | ตรวจสอบ production build ด้วย `npm run build` | Build / Frontend | Vite build สำเร็จ สร้าง asset ใน `public/build` | Pass | ทดสอบ 1 รอบ, ไม่ผ่าน 0 รอบ, build time ประมาณ 38.94 วินาที |

## TestCase Master

| TC | Category | Module | Test Objective | Preconditions | Test Steps | Expected Result | Priority | Test Type | Current Coverage |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| TC-01 | Functional | Authentication | ตรวจสอบการเข้าสู่ระบบด้วยบัญชีถูกต้อง | มี user ที่ active | เปิดหน้า login, กรอก email/password, submit | เข้าสู่ระบบสำเร็จและ redirect ตาม role | High | Automated | Covered |
| TC-02 | Security | Authentication | ตรวจสอบการเข้าสู่ระบบด้วยรหัสผ่านผิด | มี user ในระบบ | กรอก password ผิด, submit | ระบบปฏิเสธการเข้าสู่ระบบ | High | Automated | Covered |
| TC-03 | Security | Authentication | ตรวจสอบ inactive user ไม่สามารถ login | มี user status inactive | login ด้วยบัญชี inactive | ระบบไม่อนุญาตให้เข้าระบบ | High | Automated | Covered |
| TC-04 | Security | RBAC | ตรวจสอบสิทธิ์ admin ต่อเมนูจัดการผู้ใช้ | login เป็น admin และ non-admin | เข้า route users ด้วยแต่ละ role | admin เข้าได้, non-admin เข้าไม่ได้ | High | Automated | Covered |
| TC-05 | Functional | User Management | ตรวจสอบการเพิ่มผู้ใช้ใหม่ | login เป็น admin | เปิด users, submit ข้อมูลผู้ใช้ครบถ้วน | สร้าง user สำเร็จ | High | Automated | Covered |
| TC-06 | Validation | User Management | ตรวจสอบการเพิ่มผู้ใช้ซ้ำ email | มี email อยู่แล้ว | submit user ใหม่ด้วย email ซ้ำ | ระบบแจ้ง validation error | High | Automated | Covered |
| TC-07 | Functional | Assignment Data | ตรวจสอบ admin สร้าง assignment ได้ | login เป็น admin | submit assignment data ครบถ้วน | บันทึก assignment สำเร็จ | High | Automated | Covered |
| TC-08 | Validation | Assignment Data | ตรวจสอบ reviewer roles ใช้ user ซ้ำไม่ได้ | login เป็น admin | เลือก reviewer หลาย role เป็นคนเดียวกัน | ระบบแจ้ง validation error | High | Automated | Covered |
| TC-09 | Validation | Assignment Data | ตรวจสอบลำดับ reviewer ซ้ำไม่ได้ | login เป็น admin | เลือก reviewer stages ที่มี order ซ้ำ | ระบบแจ้ง validation error | Medium | Automated | Covered |
| TC-10 | Functional | Report Structure | ตรวจสอบการสร้างโครงสร้าง criteria | login เป็น admin | submit criteria structure | สร้าง criteria version สำเร็จ | High | Automated | Covered |
| TC-11 | Validation | Report Structure | ตรวจสอบ criteria ที่ข้อมูลไม่ครบ | login เป็น admin | submit criteria โดยขาดข้อมูลบังคับ | ระบบแจ้ง validation error | High | Automated | Covered |
| TC-12 | Functional | Evaluatee Flow | ผู้รับการประเมิน save draft scores ได้ | login เป็นผู้รับการประเมิน มี report ที่ assigned | กรอกคะแนนแล้ว save draft | ระบบบันทึก draft และ history | High | Automated | Covered |
| TC-13 | Functional | Evaluatee Flow | ผู้รับการประเมิน submit ไปยังผู้ประเมินได้ | login เป็นผู้รับการประเมิน | กรอกคะแนนครบแล้ว submit | สถานะ report ไป phase ผู้ประเมิน | High | Automated | Covered |
| TC-14 | Security | Evaluatee Flow | ผู้รับการประเมินแก้ report phase อื่นไม่ได้ | report อยู่ phase evaluator/director/manager/completed | พยายามแก้คะแนน | ระบบปฏิเสธการแก้ไข | High | Automated | Covered |
| TC-15 | Functional | Evaluator Flow | ผู้ประเมิน save draft และ submit ไปกรรมการได้ | login เป็นผู้ประเมิน | กรอกคะแนน/ความคิดเห็นแล้ว submit | สถานะ report ไป phase กรรมการ | High | Automated | Covered |
| TC-16 | Security | Evaluator Flow | ผู้ประเมินแก้ report phase director/manager/completed ไม่ได้ | report อยู่ phase อื่น | พยายามแก้คะแนน | ระบบปฏิเสธการแก้ไข | High | Automated | Covered |
| TC-17 | Functional | Director Flow | กรรมการ save draft และ submit ไปผู้บริหารได้ | login เป็นกรรมการ | กรอกคะแนนแล้ว submit | สถานะ report ไป phase ผู้บริหาร | High | Automated | Covered |
| TC-18 | Security | Director Flow | กรรมการเข้า report เฉพาะสถานะที่เกี่ยวข้องได้ | login เป็นกรรมการ | เปิด report หลายสถานะ | เข้าได้เฉพาะ director assigned/draft และ readonly ตามเงื่อนไข | High | Automated | Covered |
| TC-19 | Functional | Manager Flow | ผู้บริหาร save draft และ submit evaluation ได้ | login เป็นผู้บริหาร | กรอกคะแนนแล้ว submit | report ถูก submit สำเร็จ | High | Automated | Covered |
| TC-20 | Security | Manager Flow | ผู้บริหารแก้ completed report ไม่ได้ | report completed | พยายามแก้คะแนน | ระบบปฏิเสธการแก้ไข | High | Automated | Covered |
| TC-21 | Functional | Dashboard | ตรวจสอบ guest ถูก redirect ไป login | ยังไม่ login | เปิด `/dashboard` | redirect ไป login | High | Automated | Covered |
| TC-22 | Functional | Dashboard | ผู้ใช้ authenticated เปิด dashboard ได้ | login สำเร็จ | เปิด `/dashboard` | response สำเร็จ | Medium | Automated | Covered |
| TC-23 | Functional | Notification | แจ้งเตือนผู้รับการประเมินที่ยังไม่ส่ง report | มี report ยังไม่ completed และยังไม่เลยกำหนด | รัน notification command/job | ส่ง notification ตามเงื่อนไข | Medium | Automated | Covered |
| TC-24 | Functional | Notification | ไม่แจ้งเตือนเมื่อ report completed หรือเลยกำหนด | มี report completed/past end date | รัน notification command/job | ไม่ส่ง notification | Medium | Automated | Covered |
| TC-25 | Functional | Settings | admin จัดการ department/position/settings ได้ | login เป็น admin | create/update/delete setting data | บันทึกสำเร็จและ validate duplicate | Medium | Automated | Covered |
| TC-26 | Build | Frontend | ตรวจสอบ frontend build สำหรับ production | dependency พร้อม | รัน `npm run build` | build สำเร็จ ไม่มี error | High | Automated | Covered |

## Unit Test Cases

| Unit TC ID | Module | Component/Function | Description | Framework | Expected Output | Status |
| --- | --- | --- | --- | --- | --- | --- |
| UT-AUTH-001 | Auth | Login flow | ตรวจสอบ login/logout/password reset/email verification | Pest / Laravel | HTTP response และ redirect ถูกต้อง | Pass |
| UT-USER-001 | User Management | UserController | ตรวจสอบ CRUD user และ validation | Pest / Laravel | admin ทำงานได้, non-admin ถูกปฏิเสธ | Pass |
| UT-ASSIGN-001 | Assignment Data | AssignmentDataController | ตรวจสอบ CRUD assignment และ validation reviewer | Pest / Laravel | บันทึกถูกต้องและ reject ข้อมูลผิด | Pass |
| UT-REPORT-001 | Report Structure | ReportStructureController | ตรวจสอบ create/edit/delete criteria structure | Pest / Laravel | criteria ถูกจัดการตามเงื่อนไข | Pass |
| UT-EVAL-001 | Evaluation Flow | Evaluatee/Evaluator/Director/Manager controllers | ตรวจสอบ workflow และ permission ตาม phase | Pest / Laravel | role เข้าถึงและแก้ไขได้ตามสิทธิ์ | Pass |
| UT-NOTIFY-001 | Notification | NotifyEndDate / status update | ตรวจสอบการแจ้งเตือนและเปลี่ยนสถานะ report | Pest / Laravel | ส่งหรือไม่ส่งตามเงื่อนไข | Pass |
| UT-SETTING-001 | Settings | Department/Position/University/Profile/Password | ตรวจสอบ settings CRUD และ validation | Pest / Laravel | ทำงานถูกต้องตาม role และข้อมูล | Pass |

## Defect Register

| Defect ID | Module | Severity | Description | Related Test Case | Action Taken | Status |
| --- | --- | --- | --- | --- | --- | --- |
| - | - | - | ไม่พบ defect จาก automated regression และ frontend build รอบนี้ | - | - | - |

## Fail Case Analysis

| No. | Test Case / Round | Failed Count | What Happened | Failure Reason | Impact | Corrective Action | Status |
| --- | --- | ---: | --- | --- | --- | --- | --- |
| - | Automated regression / build verification | 0 | ไม่พบ test case ที่ไม่ผ่าน | N/A | ไม่มีผลกระทบจากการทดสอบรอบนี้ | N/A | Closed |

ถ้ารอบถัดไปมี test ไม่ผ่าน ให้บันทึกข้อมูลอย่างน้อยดังนี้:

| Field | Description |
| --- | --- |
| What Happened | อาการที่เกิดขึ้นจริง เช่น login ไม่ได้, redirect ผิดหน้า, validation ไม่แสดง |
| Failure Reason | สาเหตุที่คาดว่าเกิด เช่น logic ผิด, permission ไม่ครบ, database state ไม่ถูกต้อง |
| Expected Result | ระบบควรทำงานอย่างไรตาม requirement |
| Actual Result | ระบบทำงานจริงอย่างไร |
| Impact | กระทบผู้ใช้หรือ flow ไหน ระดับรุนแรงเท่าไร |
| Corrective Action | วิธีแก้ไขหรือสิ่งที่ต้องตรวจต่อ |
| Retest Result | หลังแก้แล้ว test ซ้ำผ่านหรือไม่ |

## Commands Executed

```bash
composer test
npm run build
```

## Notes

- ผลนี้เป็น automated regression test และ build verification จาก test suite ที่มีอยู่ใน repository
- ยังไม่ได้ทำ manual browser walkthrough หรือ exploratory testing ด้วยข้อมูลจริง
- ถ้าต้องส่งเป็น Excel ตาม template เดิม สามารถนำตารางในรายงานนี้ไปใส่ใน sheet `TestCase_Master`, `Unit_Test_Cases`, `Test_Execution_Log`, `Defect_Register`, และ `Dashboard_Summary` ได้ทันที
