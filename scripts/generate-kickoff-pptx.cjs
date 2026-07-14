const fs = require('fs');
const path = require('path');
const PptxGenJS = require('pptxgenjs');

const OUT_PATH = path.join('docs', 'kickoff', '2026-07-15-kickoff-system-preview.pptx');
const LOGO_PATH = path.join('public', 'favicon-msu.png');

const FONT = 'Tahoma';
const COLORS = {
    background: 'F7FAFC',
    navy: '17324D',
    teal: '2A7F80',
    tealLight: 'DDF1EF',
    amber: 'D97706',
    amberLight: 'FFF2D8',
    green: '2F855A',
    greenLight: 'E4F4EA',
    blue: '2B6CB0',
    blueLight: 'E6F0FA',
    red: 'B83232',
    redLight: 'FBE8E8',
    slate: '486174',
    muted: '6B7F90',
    border: 'D9E3EA',
    white: 'FFFFFF',
};

const AGENDA_MINUTES = [5, 5, 10, 15, 10, 15];

const SLIDES = [
    {
        id: 'opening',
        title: 'Kickoff โครงการ Workload Management Module Phase 2',
        eyebrow: 'คณะสาธารณสุขศาสตร์ มหาวิทยาลัยมหาสารคาม',
        subtitle: 'เชื่อมการบันทึกภาระงาน การคำนวณคะแนน และระบบประเมินผลหลัก เวอร์ชัน 2026',
        date: '15 กรกฎาคม 2569',
        outcomes: ['เข้าใจขอบเขตตรงกัน', 'เห็นฟีเจอร์และสถานะจริง', 'ยืนยันแผนดำเนินงาน', 'กำหนดเจ้าของงานถัดไป'],
        notes:
            'เปิดประชุมโดยแจ้งว่าเป็น Kickoff พร้อม System Preview และระบุผลลัพธ์ 4 ข้อ หากมีคำถามที่ยังตอบไม่ได้ ให้บันทึกเป็น Action Item พร้อมผู้รับผิดชอบและวันที่ตอบ',
    },
    {
        id: 'objective',
        title: 'ปัญหาและเป้าหมายโครงการ',
        problems: [
            { number: '01', title: 'คำนวณนอกโมดูล', text: 'คำนวณคะแนนภาระงานแยกจากแบบประเมิน' },
            { number: '02', title: 'กรอกคะแนนซ้ำ', text: 'นำคะแนนกลับมากรอกซ้ำในแบบประเมิน' },
            { number: '03', title: 'ตรวจสอบยาก', text: 'ใช้เวลาและเสี่ยงต่อความคลาดเคลื่อนของข้อมูล' },
        ],
        goal: 'Phase 2 เพิ่มการบันทึกภาระงาน การแนบหลักฐาน และการคำนวณคะแนนอัตโนมัติภายในระบบประเมินเดิม',
        notes:
            'อธิบายปัญหาการคำนวณคะแนนภาระงานนอกโมดูลและการนำคะแนนกลับมากรอกซ้ำ แล้วเน้นว่า Phase 2 ทำงานภายในระบบประเมินเดิม',
    },
    {
        id: 'features',
        title: 'ฟีเจอร์หลักของ Phase 2',
        subtitle: 'โมดูลบริหารจัดการภาระงานภายในระบบประเมินเดิม',
        features: [
            {
                roles: ['ผู้ดูแลระบบ'],
                title: 'กำหนดแบบฟอร์มภาระงาน',
                color: 'green',
            },
            {
                roles: ['ผู้ดูแลระบบ'],
                title: 'กำหนดสูตรคำนวณคะแนน',
                color: 'blue',
            },
            {
                roles: ['ผู้รับการประเมิน'],
                title: 'บันทึกและแก้ไขข้อมูลภาระงาน',
                color: 'teal',
            },
            {
                roles: ['ผู้รับการประเมิน'],
                title: 'แนบลิงก์หลักฐาน',
                color: 'green',
            },
            {
                roles: ['ระบบ'],
                title: 'คำนวณคะแนนและนำไปใช้ในแบบประเมิน',
                color: 'blue',
            },
            {
                roles: ['ผู้ดูแลระบบ', 'ผู้บริหาร', 'กรรมการ', 'ผู้ประเมิน'],
                title: 'รายงานและส่งออกข้อมูลภาระงาน',
                color: 'teal',
            },
        ],
        notes:
            'Phase 2 มีฟีเจอร์หลักหกส่วนที่ทำงานต่อเนื่องกันครับ เริ่มจากผู้ดูแลระบบกำหนดแบบฟอร์มภาระงานและสูตรคำนวณคะแนน จากนั้นผู้รับการประเมินบันทึกหรือแก้ไขข้อมูลภาระงานพร้อมแนบลิงก์หลักฐาน เมื่อบันทึกแล้วระบบจะคำนวณคะแนนอัตโนมัติและนำคะแนนไปใช้ในแบบประเมิน สุดท้ายผู้ที่มีสิทธิ์สามารถดูรายงานและส่งออกข้อมูลภาระงานได้ ทั้งหมดนี้ทำงานอยู่ภายในระบบประเมินเดิมครับ',
    },
    {
        id: 'demo',
        title: 'สาธิต Workflow จาก Admin ถึง Evaluatee',
        steps: [
            { label: 'Admin', detail: 'กำหนดแบบฟอร์ม' },
            { label: 'สูตร', detail: 'ตัวแปรและพรีวิว' },
            { label: 'Evaluatee', detail: 'บันทึกภาระงาน' },
            { label: 'หลักฐาน', detail: 'แนบลิงก์อ้างอิง' },
            { label: 'คะแนน', detail: 'คำนวณอัตโนมัติ' },
        ],
        guardrail: 'เดโมด้วยข้อมูลตัวอย่าง • ไม่แก้ข้อมูลจริง • แสดงคะแนนในรายงานและแบบประเมินเดียวกัน',
        notes:
            'เดโมตามลำดับเดียว ไม่เปิดเมนูที่ไม่เกี่ยวข้อง เริ่มจาก Workload Config ชี้ Field และสูตร จากนั้นไปมุมผู้รับการประเมินเพื่อชี้ข้อมูล หลักฐาน และคะแนน ปิดด้วยการแสดงว่าคะแนนภาระงานถูกบันทึกและนำไปใช้กับรายงานการประเมินเดียวกัน',
    },
    {
        id: 'timeline',
        title: 'แผนดำเนินงาน 90 วัน',
        phases: [
            { weeks: 'สัปดาห์ 1–2', title: 'Analysis & Design', detail: 'เก็บความต้องการและยืนยันแบบฟอร์ม/สูตร' },
            { weeks: 'สัปดาห์ 3–8', title: 'Development', detail: 'พัฒนา Backend และ Frontend' },
            { weeks: 'สัปดาห์ 9–10', title: 'ทดสอบ Workflow & UAT', detail: 'ทดสอบกับกระบวนการประเมินและรวบรวมข้อแก้ไข' },
            { weeks: 'สัปดาห์ 11–12', title: 'Deploy & Hypercare', detail: 'แก้ไข ติดตั้ง คู่มือ อบรม และดูแลระบบ' },
        ],
        decision: 'ต้องยืนยันวันเริ่มนับ 90 วัน: วันลงนาม 29 มิ.ย. 2569 หรือวัน Kickoff 15 ก.ค. 2569',
        deliverables: 'Source Code + ฐานข้อมูล • คู่มือ Admin / Evaluator / Evaluatee • ผล UAT • การติดตั้งและอบรม',
        notes:
            'อธิบายสี่ช่วงตาม TOR และขอให้ที่ประชุมยืนยันวันเริ่มนับอย่างชัดเจน ห้ามกำหนดวันส่งมอบใหม่แทนผู้มีอำนาจ',
    },
    {
        id: 'next',
        title: 'เรื่องที่ต้องได้ข้อสรุปและขั้นตอนถัดไป',
        decisions: [
            'แบบฟอร์มและสูตรชุดแรก พร้อมผู้อนุมัติ',
            'การปัดเศษ ข้อมูลว่าง และกรณีสูตรผิดพลาด',
            'การจับคู่คะแนนกับรายงาน เกณฑ์ย่อย และผู้ตรวจสอบความถูกต้อง',
            'รูปแบบรายงาน PDF/Excel และข้อมูลตัวอย่าง',
            'คณะกรรมการ UAT ผู้รับรอง และนิยาม Critical',
            'วันเริ่มนับ 90 วัน ช่องทางสื่อสาร และกำหนดตอบกลับ',
        ],
        close: 'ทุกข้อสรุปต้องมี “ผู้รับผิดชอบ + กำหนดส่ง” ก่อนจบประชุม',
        notes:
            'ถามทีละหัวข้อ รอให้ผู้จดบันทึกใส่ชื่อและวันที่ ก่อนปิดประชุมให้อ่านทวนมติ Action Item ผู้รับผิดชอบ และกำหนดส่งทั้งหมด',
    },
];

function addText(slide, text, options = {}) {
    slide.addText(text, {
        fontFace: FONT,
        color: COLORS.navy,
        margin: 0,
        breakLine: false,
        fit: 'shrink',
        valign: 'mid',
        ...options,
    });
}

function addCard(slide, x, y, w, h, fill = COLORS.white, line = COLORS.border, radius = true) {
    slide.addShape(radius ? 'roundRect' : 'rect', {
        x,
        y,
        w,
        h,
        rectRadius: radius ? 0.08 : 0,
        fill: { color: fill },
        line: { color: line, width: 1 },
        shadow: { type: 'outer', color: 'AAB7C2', opacity: 0.12, blur: 1, angle: 45, distance: 1 },
    });
}

function addHeader(slide, definition, index) {
    slide.background = { color: COLORS.background };
    slide.addShape('rect', {
        x: 0,
        y: 0,
        w: 0.16,
        h: 7.5,
        fill: { color: COLORS.teal },
        line: { color: COLORS.teal },
    });
    addText(slide, definition.title, {
        x: 0.56,
        y: 0.32,
        w: 11.65,
        h: 0.55,
        fontSize: 27,
        bold: true,
    });
    addText(slide, `0${index}`, {
        x: 12.23,
        y: 0.32,
        w: 0.55,
        h: 0.45,
        fontSize: 13,
        color: COLORS.muted,
        align: 'right',
    });
    slide.addShape('line', {
        x: 0.56,
        y: 1.02,
        w: 12.12,
        h: 0,
        line: { color: COLORS.border, width: 1 },
    });
}

function addFooter(slide, index) {
    addText(slide, 'Workload Management Module Phase 2', {
        x: 0.56,
        y: 7.14,
        w: 5.2,
        h: 0.2,
        fontSize: 9,
        color: COLORS.muted,
    });
    addText(slide, `15 กรกฎาคม 2569  •  ${index}/6`, {
        x: 9.9,
        y: 7.14,
        w: 2.75,
        h: 0.2,
        fontSize: 9,
        color: COLORS.muted,
        align: 'right',
    });
}

function addLogo(slide, x, y, w, h) {
    if (fs.existsSync(LOGO_PATH)) {
        slide.addImage({ path: LOGO_PATH, x, y, w, h, transparency: 2 });
    }
}

function renderOpening(slide, definition) {
    slide.background = { color: COLORS.background };
    slide.addShape('rect', {
        x: 0,
        y: 0,
        w: 4.05,
        h: 7.5,
        fill: { color: COLORS.navy },
        line: { color: COLORS.navy },
    });
    slide.addShape('rect', {
        x: 3.75,
        y: 0,
        w: 0.3,
        h: 7.5,
        fill: { color: COLORS.teal },
        line: { color: COLORS.teal },
    });
    addLogo(slide, 1.27, 0.65, 1.5, 1.5);
    addText(slide, 'KICKOFF', {
        x: 0.65,
        y: 2.42,
        w: 2.8,
        h: 0.48,
        fontSize: 24,
        bold: true,
        color: COLORS.white,
        charSpacing: 4,
    });
    addText(slide, definition.date, {
        x: 0.65,
        y: 5.95,
        w: 2.8,
        h: 0.35,
        fontSize: 17,
        color: 'CFE1EC',
    });
    addText(slide, 'Microsoft Teams', {
        x: 0.65,
        y: 6.38,
        w: 2.8,
        h: 0.3,
        fontSize: 13,
        color: '9FC4C3',
    });

    addText(slide, definition.eyebrow, {
        x: 4.7,
        y: 0.72,
        w: 7.7,
        h: 0.34,
        fontSize: 15,
        bold: true,
        color: COLORS.teal,
    });
    addText(slide, definition.title, {
        x: 4.7,
        y: 1.28,
        w: 7.65,
        h: 1.28,
        fontSize: 30,
        bold: true,
        color: COLORS.navy,
        valign: 'top',
    });
    addText(slide, definition.subtitle, {
        x: 4.7,
        y: 2.75,
        w: 7.55,
        h: 0.78,
        fontSize: 18,
        color: COLORS.slate,
        valign: 'top',
    });
    addText(slide, 'ผลลัพธ์ที่ต้องได้จากวันนี้', {
        x: 4.7,
        y: 4.03,
        w: 4.4,
        h: 0.35,
        fontSize: 16,
        bold: true,
        color: COLORS.navy,
    });
    definition.outcomes.forEach((outcome, index) => {
        const x = 4.7 + (index % 2) * 3.77;
        const y = 4.55 + Math.floor(index / 2) * 1.02;
        addCard(slide, x, y, 3.48, 0.72, index === 3 ? COLORS.tealLight : COLORS.white);
        addText(slide, String(index + 1).padStart(2, '0'), {
            x: x + 0.18,
            y: y + 0.16,
            w: 0.42,
            h: 0.35,
            fontSize: 13,
            bold: true,
            color: COLORS.teal,
        });
        addText(slide, outcome, {
            x: x + 0.65,
            y: y + 0.11,
            w: 2.58,
            h: 0.46,
            fontSize: 15,
            bold: true,
        });
    });
}

function renderObjective(slide, definition) {
    addText(slide, 'สถานการณ์เดิม', {
        x: 0.62,
        y: 1.25,
        w: 2.2,
        h: 0.3,
        fontSize: 16,
        bold: true,
        color: COLORS.amber,
    });
    definition.problems.forEach((problem, index) => {
        const x = 0.62 + index * 4.13;
        addCard(slide, x, 1.7, 3.75, 2.2, COLORS.white);
        addText(slide, problem.number, {
            x: x + 0.22,
            y: 1.94,
            w: 0.58,
            h: 0.4,
            fontSize: 18,
            bold: true,
            color: COLORS.amber,
        });
        addText(slide, problem.title, {
            x: x + 0.24,
            y: 2.47,
            w: 3.1,
            h: 0.45,
            fontSize: 19,
            bold: true,
        });
        addText(slide, problem.text, {
            x: x + 0.24,
            y: 3.02,
            w: 3.18,
            h: 0.62,
            fontSize: 14,
            color: COLORS.slate,
            valign: 'top',
        });
    });
    addCard(slide, 0.62, 4.42, 12.02, 1.8, COLORS.tealLight, '9FCBC7');
    addText(slide, 'เป้าหมาย Phase 2', {
        x: 0.98,
        y: 4.78,
        w: 2.55,
        h: 0.42,
        fontSize: 16,
        bold: true,
        color: COLORS.teal,
    });
    addText(slide, definition.goal, {
        x: 3.45,
        y: 4.7,
        w: 8.55,
        h: 0.75,
        fontSize: 20,
        bold: true,
        color: COLORS.navy,
    });
    addText(slide, 'ลดงานซ้ำ  •  ลดความคลาดเคลื่อน  •  ตรวจสอบย้อนกลับได้', {
        x: 3.15,
        y: 5.5,
        w: 8.85,
        h: 0.34,
        fontSize: 14,
        color: COLORS.teal,
    });
}

function featurePalette(name) {
    if (name === 'green') return { strong: COLORS.green, light: COLORS.greenLight };
    if (name === 'blue') return { strong: COLORS.blue, light: COLORS.blueLight };
    return { strong: COLORS.teal, light: COLORS.tealLight };
}

function renderFeatures(slide, definition) {
    addText(slide, definition.subtitle, {
        x: 0.7,
        y: 1.16,
        w: 11.9,
        h: 0.35,
        fontSize: 14,
        color: COLORS.slate,
        align: 'center',
    });

    definition.features.forEach((feature, index) => {
        const palette = featurePalette(feature.color);
        const column = index % 3;
        const row = Math.floor(index / 3);
        const x = 0.62 + column * 4.13;
        const y = 1.72 + row * 2.36;
        const cardWidth = 3.78;
        const cardHeight = 2.08;
        const hasMultipleRoles = feature.roles.length > 1;

        addCard(slide, x, y, cardWidth, cardHeight, COLORS.white, COLORS.border);
        slide.addShape('rect', {
            x,
            y,
            w: cardWidth,
            h: 0.12,
            fill: { color: palette.strong },
            line: { color: palette.strong },
        });

        feature.roles.forEach((role, roleIndex) => {
            const pillColumn = hasMultipleRoles ? roleIndex % 2 : 0;
            const pillRow = hasMultipleRoles ? Math.floor(roleIndex / 2) : 0;
            const pillWidth = hasMultipleRoles ? 1.48 : role === 'ผู้รับการประเมิน' ? 1.62 : 1.28;
            const pillX = hasMultipleRoles ? x + 0.29 + pillColumn * 1.62 : x + 0.24;
            const pillY = y + 0.3 + pillRow * 0.36;

            slide.addShape('roundRect', {
                x: pillX,
                y: pillY,
                w: pillWidth,
                h: 0.28,
                rectRadius: 0.06,
                fill: { color: palette.light },
                line: { color: palette.light },
            });
            addText(slide, role, {
                x: pillX + 0.05,
                y: pillY + 0.035,
                w: pillWidth - 0.1,
                h: 0.2,
                fontSize: hasMultipleRoles ? 9 : 10,
                bold: true,
                color: palette.strong,
                align: 'center',
                valign: 'mid',
                margin: 0,
            });
        });

        addText(slide, feature.title, {
            x: x + 0.24,
            y: y + (hasMultipleRoles ? 1.03 : 0.82),
            w: cardWidth - 0.48,
            h: hasMultipleRoles ? 0.78 : 0.9,
            fontSize: hasMultipleRoles ? 16.5 : 18,
            bold: true,
            color: COLORS.navy,
            align: 'center',
            valign: 'mid',
            margin: 0.04,
        });
    });
}

function renderDemo(slide, definition) {
    addText(slide, 'หนึ่ง Workflow ที่ต่อเนื่อง', {
        x: 0.62,
        y: 1.25,
        w: 3.3,
        h: 0.3,
        fontSize: 15,
        bold: true,
        color: COLORS.teal,
    });
    definition.steps.forEach((step, index) => {
        const x = 0.64 + index * 2.48;
        const fill = index === 0 ? COLORS.navy : index === 4 ? COLORS.teal : 'DDE8EF';
        const color = index === 0 || index === 4 ? COLORS.white : COLORS.navy;
        slide.addShape(index === 4 ? 'roundRect' : 'chevron', {
            x,
            y: 2.02,
            w: 2.25,
            h: 1.45,
            fill: { color: fill },
            line: { color: fill },
        });
        addText(slide, String(index + 1), {
            x: x + 0.5,
            y: 2.22,
            w: 0.35,
            h: 0.28,
            fontSize: 11,
            bold: true,
            color,
            align: 'center',
        });
        addText(slide, step.label, {
            x: x + 0.64,
            y: 2.42,
            w: 1.22,
            h: 0.36,
            fontSize: 17,
            bold: true,
            color,
            align: 'center',
        });
        addText(slide, step.detail, {
            x: x + 0.58,
            y: 2.82,
            w: 1.3,
            h: 0.38,
            fontSize: 11,
            color,
            align: 'center',
            valign: 'top',
        });
    });
    addCard(slide, 0.78, 4.23, 11.7, 1.38, COLORS.white);
    addText(slide, 'จุดพูดหลัก', {
        x: 1.08,
        y: 4.56,
        w: 1.45,
        h: 0.34,
        fontSize: 16,
        bold: true,
        color: COLORS.teal,
    });
    addText(slide, 'Admin กำหนดโครงสร้าง → Evaluatee บันทึกข้อมูลและหลักฐาน → ระบบคำนวณคะแนนตามสูตร', {
        x: 2.5,
        y: 4.45,
        w: 9.3,
        h: 0.58,
        fontSize: 18,
        bold: true,
    });
    addText(slide, definition.guardrail, {
        x: 1.08,
        y: 5.82,
        w: 11.1,
        h: 0.42,
        fontSize: 13,
        bold: true,
        color: COLORS.red,
        align: 'center',
    });
}

function renderTimeline(slide, definition) {
    slide.addShape('line', {
        x: 1.25,
        y: 3.02,
        w: 10.85,
        h: 0,
        line: { color: COLORS.border, width: 5 },
    });
    definition.phases.forEach((phase, index) => {
        const x = 0.65 + index * 3.08;
        const color = index < 2 ? COLORS.teal : index === 2 ? COLORS.blue : COLORS.amber;
        slide.addShape('ellipse', {
            x: x + 1.05,
            y: 2.74,
            w: 0.55,
            h: 0.55,
            fill: { color },
            line: { color: COLORS.white, width: 2 },
        });
        addText(slide, phase.weeks, {
            x,
            y: 1.37,
            w: 2.65,
            h: 0.34,
            fontSize: 14,
            bold: true,
            color,
            align: 'center',
        });
        addText(slide, phase.title, {
            x,
            y: 1.83,
            w: 2.65,
            h: 0.42,
            fontSize: 17,
            bold: true,
            align: 'center',
        });
        addText(slide, phase.detail, {
            x,
            y: 3.52,
            w: 2.65,
            h: 0.78,
            fontSize: 12,
            color: COLORS.slate,
            align: 'center',
            valign: 'top',
        });
    });
    addCard(slide, 0.75, 4.72, 11.82, 0.92, COLORS.amberLight, 'F1C27D');
    addText(slide, 'ต้องยืนยัน', {
        x: 1.02,
        y: 4.98,
        w: 1.3,
        h: 0.34,
        fontSize: 14,
        bold: true,
        color: COLORS.amber,
    });
    addText(slide, definition.decision, {
        x: 2.3,
        y: 4.89,
        w: 9.72,
        h: 0.48,
        fontSize: 15,
        bold: true,
        color: COLORS.navy,
    });
    addText(slide, definition.deliverables, {
        x: 0.85,
        y: 5.98,
        w: 11.6,
        h: 0.48,
        fontSize: 13,
        color: COLORS.slate,
        align: 'center',
    });
}

function renderNext(slide, definition) {
    addText(slide, 'ปิดความไม่ชัดเจนก่อนเริ่มช่วงงานถัดไป', {
        x: 0.62,
        y: 1.23,
        w: 5.2,
        h: 0.34,
        fontSize: 15,
        bold: true,
        color: COLORS.teal,
    });
    definition.decisions.forEach((decision, index) => {
        const column = index % 2;
        const row = Math.floor(index / 2);
        const x = 0.68 + column * 6.12;
        const y = 1.82 + row * 1.18;
        addCard(slide, x, y, 5.68, 0.9, COLORS.white);
        addText(slide, String(index + 1).padStart(2, '0'), {
            x: x + 0.22,
            y: y + 0.22,
            w: 0.5,
            h: 0.35,
            fontSize: 12,
            bold: true,
            color: COLORS.teal,
            align: 'center',
        });
        addText(slide, decision, {
            x: x + 0.88,
            y: y + 0.14,
            w: 4.48,
            h: 0.55,
            fontSize: 14,
            bold: true,
            color: COLORS.navy,
            valign: 'top',
        });
    });
    addCard(slide, 0.68, 5.64, 11.8, 0.82, COLORS.tealLight, '9FCBC7');
    addText(slide, definition.close, {
        x: 1.02,
        y: 5.85,
        w: 11.12,
        h: 0.36,
        fontSize: 18,
        bold: true,
        color: COLORS.teal,
        align: 'center',
    });
}

function addKickoffSlide(pptx, definition, index) {
    const slide = pptx.addSlide();

    if (definition.id === 'opening') {
        renderOpening(slide, definition);
    } else {
        addHeader(slide, definition, index);
        if (definition.id === 'objective') renderObjective(slide, definition);
        if (definition.id === 'features') renderFeatures(slide, definition);
        if (definition.id === 'demo') renderDemo(slide, definition);
        if (definition.id === 'timeline') renderTimeline(slide, definition);
        if (definition.id === 'next') renderNext(slide, definition);
        addFooter(slide, index);
    }

    slide.addNotes(definition.notes);
}

function buildKickoffDeck() {
    const pptx = new PptxGenJS();
    pptx.layout = 'LAYOUT_WIDE';
    pptx.author = 'MSU EVA Project Team';
    pptx.company = 'Workload Management Module Phase 2';
    pptx.subject = 'Kickoff และสาธิต Workload Management Module Phase 2';
    pptx.title = 'Kickoff Workload Management Module Phase 2';
    pptx.lang = 'th-TH';
    pptx.theme = {
        headFontFace: FONT,
        bodyFontFace: FONT,
        lang: 'th-TH',
    };

    SLIDES.forEach((definition, index) => addKickoffSlide(pptx, definition, index + 1));

    return pptx;
}

async function generateKickoffDeck() {
    fs.mkdirSync(path.dirname(OUT_PATH), { recursive: true });
    const pptx = buildKickoffDeck();
    await pptx.writeFile({ fileName: OUT_PATH, compression: true });
    console.log(`Generated ${OUT_PATH}`);
}

if (require.main === module) {
    generateKickoffDeck().catch((error) => {
        console.error(error);
        process.exitCode = 1;
    });
}

module.exports = { AGENDA_MINUTES, SLIDES, buildKickoffDeck, generateKickoffDeck };
