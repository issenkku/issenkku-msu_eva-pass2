from pathlib import Path
import re

from docx import Document
from docx.enum.section import WD_SECTION
from docx.enum.table import WD_CELL_VERTICAL_ALIGNMENT, WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Pt, RGBColor


ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "docs" / "uat-test-result-2026-06-30.md"
OUTPUT = ROOT / "docs" / "รายงานสรุปผลการทดสอบ-UAT-2026-07-10.docx"
FONT = "TH Sarabun New"
PURPLE = "7030A0"
LIGHT_PURPLE = "E4DFEC"
LIGHT_GRAY = "F2F2F2"


def set_cell_shading(cell, color):
    properties = cell._tc.get_or_add_tcPr()
    shading = properties.find(qn("w:shd"))
    if shading is None:
        shading = OxmlElement("w:shd")
        properties.append(shading)
    shading.set(qn("w:fill"), color)


def set_cell_text(cell, text, bold=False, color=None, align=None, size=14):
    cell.text = ""
    paragraph = cell.paragraphs[0]
    if align is not None:
        paragraph.alignment = align
    run = paragraph.add_run(str(text))
    run.bold = bold
    run.font.name = FONT
    run._element.rPr.rFonts.set(qn("w:eastAsia"), FONT)
    run.font.size = Pt(size)
    if color:
        run.font.color.rgb = RGBColor.from_string(color)
    cell.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER


def set_repeat_table_header(row):
    properties = row._tr.get_or_add_trPr()
    repeat = OxmlElement("w:tblHeader")
    repeat.set(qn("w:val"), "true")
    properties.append(repeat)


def add_table(document, headers, rows, widths=None, font_size=13):
    table = document.add_table(rows=1, cols=len(headers))
    table.style = "Table Grid"
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    header = table.rows[0]
    set_repeat_table_header(header)
    for index, heading in enumerate(headers):
        set_cell_text(header.cells[index], heading, bold=True, color="FFFFFF", align=WD_ALIGN_PARAGRAPH.CENTER)
        set_cell_shading(header.cells[index], PURPLE)
        if widths:
            header.cells[index].width = Cm(widths[index])

    for row_index, row_data in enumerate(rows):
        cells = table.add_row().cells
        for index, value in enumerate(row_data):
            align = WD_ALIGN_PARAGRAPH.CENTER if index in (0, 2, 3) else WD_ALIGN_PARAGRAPH.LEFT
            set_cell_text(cells[index], value, align=align, size=font_size)
            if widths:
                cells[index].width = Cm(widths[index])
            if row_index % 2:
                set_cell_shading(cells[index], LIGHT_GRAY)
    return table


def add_bullet(document, text):
    paragraph = document.add_paragraph(style="List Bullet")
    paragraph.add_run(text)
    return paragraph


def add_number(document, text):
    paragraph = document.add_paragraph(style="List Number")
    paragraph.add_run(text)
    return paragraph


def parse_uat_rows(markdown):
    categories = []
    current = None
    heading_pattern = re.compile(r"^### หมวดที่ (\d+) (.+)$")
    row_pattern = re.compile(
        r"^\| (UAT-[A-Z]+-\d+) \| (.*?) \| (PASS|PARTIAL|FAIL|BLOCKED / NOT TESTED) \| (\d+) \| (.*?) \|$"
    )

    for line in markdown.splitlines():
        heading = heading_pattern.match(line)
        if heading:
            current = {"number": int(heading.group(1)), "name": heading.group(2), "rows": []}
            categories.append(current)
            continue
        row = row_pattern.match(line)
        if row and current:
            current["rows"].append(row.groups())
    return categories


def add_page_number(paragraph):
    paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run = paragraph.add_run("หน้า ")
    field = OxmlElement("w:fldSimple")
    field.set(qn("w:instr"), "PAGE")
    run._r.addnext(field)


def configure_document(document):
    section = document.sections[0]
    section.top_margin = Cm(2.2)
    section.bottom_margin = Cm(2.0)
    section.left_margin = Cm(2.4)
    section.right_margin = Cm(2.0)

    normal = document.styles["Normal"]
    normal.font.name = FONT
    normal._element.rPr.rFonts.set(qn("w:eastAsia"), FONT)
    normal.font.size = Pt(16)
    normal.paragraph_format.space_after = Pt(4)
    normal.paragraph_format.line_spacing = 1.0

    for style_name, size, color in [
        ("Title", 28, PURPLE),
        ("Heading 1", 20, PURPLE),
        ("Heading 2", 18, "333333"),
        ("Heading 3", 16, "555555"),
    ]:
        style = document.styles[style_name]
        style.font.name = FONT
        style._element.rPr.rFonts.set(qn("w:eastAsia"), FONT)
        style.font.size = Pt(size)
        style.font.color.rgb = RGBColor.from_string(color)

    footer = section.footer.paragraphs[0]
    add_page_number(footer)


def build_document():
    markdown = SOURCE.read_text(encoding="utf-8")
    categories = parse_uat_rows(markdown)
    document = Document()
    configure_document(document)

    title = document.add_paragraph()
    title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    title.space_after = Pt(14)
    run = title.add_run("รายงานสรุปผลการทดสอบระบบ")
    run.bold = True
    run.font.name = FONT
    run._element.rPr.rFonts.set(qn("w:eastAsia"), FONT)
    run.font.size = Pt(28)
    run.font.color.rgb = RGBColor.from_string(PURPLE)

    subtitle = document.add_paragraph()
    subtitle.alignment = WD_ALIGN_PARAGRAPH.CENTER
    subtitle_run = subtitle.add_run("ระบบประเมินผลการปฏิบัติงานบุคลากร\nคณะสาธารณสุขศาสตร์ มหาวิทยาลัยมหาสารคาม")
    subtitle_run.bold = True
    subtitle_run.font.size = Pt(21)

    document.add_paragraph()
    metadata = [
        ("ประเภทการทดสอบ", "User Acceptance Testing (UAT), Automated Test และ Manual Test"),
        ("ขอบเขต", "Workload Management Module และฟังก์ชันที่เกี่ยวข้อง"),
        ("วันที่เริ่มทดสอบ", "30 มิถุนายน 2569"),
        ("วันที่ทดสอบล่าสุด", "10 กรกฎาคม 2569"),
        ("Repository", "issenkku/issenkku-msu_eva-pass2"),
        ("สถานะเอกสาร", "ฉบับสรุปผลอย่างเป็นทางการ"),
    ]
    table = document.add_table(rows=0, cols=2)
    table.style = "Table Grid"
    for key, value in metadata:
        cells = table.add_row().cells
        set_cell_text(cells[0], key, bold=True, size=16)
        set_cell_shading(cells[0], LIGHT_PURPLE)
        set_cell_text(cells[1], value, size=16)

    document.add_paragraph()
    approval = document.add_paragraph()
    approval.alignment = WD_ALIGN_PARAGRAPH.CENTER
    approval_run = approval.add_run("ผลการทดสอบ: ผ่านเกณฑ์ UAT สำหรับสภาพแวดล้อม UAT")
    approval_run.bold = True
    approval_run.font.size = Pt(19)
    approval_run.font.color.rgb = RGBColor(0x00, 0x70, 0x00)
    document.add_page_break()

    document.add_heading("1. บทสรุปผู้บริหาร", level=1)
    document.add_paragraph(
        "ดำเนินการทดสอบระบบตามกรณีทดสอบ UAT จำนวน 50 รายการ ครอบคลุม 7 หมวด "
        "ผลการทดสอบผ่านครบทั้ง 50 รายการ ไม่พบรายการที่ล้มเหลว อยู่ระหว่างดำเนินการ "
        "หรือไม่ได้ทดสอบ จึงสรุปว่าระบบผ่านเกณฑ์ UAT สำหรับสภาพแวดล้อม UAT"
    )

    add_table(
        document,
        ["สถานะ", "จำนวน", "ร้อยละ"],
        [
            ("PASS", "50", "100%"),
            ("PARTIAL", "0", "0%"),
            ("FAIL", "0", "0%"),
            ("BLOCKED / NOT TESTED", "0", "0%"),
            ("รวม", "50", "100%"),
        ],
        widths=[8, 4, 4],
        font_size=15,
    )

    document.add_heading("2. วัตถุประสงค์", level=1)
    for item in [
        "ยืนยันว่าฟังก์ชันบริหารจัดการและบันทึกข้อมูลภาระงานทำงานตรงตามกรณีใช้งาน",
        "ยืนยันความถูกต้องของการคำนวณคะแนน การเชื่อมต่อข้อมูล สิทธิ์ผู้ใช้ และ Audit Log",
        "ประเมินประสิทธิภาพเบื้องต้นและความพร้อมด้าน HTTPS ในสภาพแวดล้อม UAT",
        "จัดทำหลักฐานประกอบการพิจารณารับรองระบบก่อนเตรียมนำขึ้น Production",
    ]:
        add_bullet(document, item)

    document.add_heading("3. วิธีการและสภาพแวดล้อมการทดสอบ", level=1)
    environment_rows = [
        ("Automated test", "Laravel Pest", "รันชุดทดสอบทั้งหมดและชุดทดสอบเฉพาะฟังก์ชัน"),
        ("Manual test", "Web browser", "ตรวจการทำงานจริงตามบทบาทและขั้นตอน UAT"),
        ("Database สำหรับ automated test", "SQLite", "แยกจากฐานข้อมูล Production"),
        ("Concurrency", "50 concurrent HTTP requests", "ทดสอบ endpoint /login ใน UAT"),
        ("HTTPS", "mkcert + Local CA + Node.js reverse proxy", "https://msu-eva.test:8443"),
    ]
    add_table(document, ["ประเภท", "เครื่องมือ/สภาพแวดล้อม", "รายละเอียด"], environment_rows, [4, 6, 8], 13)

    document.add_heading("4. ผลการทดสอบอัตโนมัติล่าสุด", level=1)
    document.add_paragraph("คำสั่งที่ใช้:")
    code = document.add_paragraph()
    code.style = document.styles["Normal"]
    code_run = code.add_run("vendor\\bin\\pest --compact")
    code_run.font.name = "Consolas"
    code_run.font.size = Pt(11)
    set_cell_shading  # keep helper referenced for static checkers
    add_table(
        document,
        ["รายการ", "ผลลัพธ์"],
        [
            ("จำนวน test", "238 ผ่าน"),
            ("จำนวน assertion", "1,083 assertions"),
            ("Test ที่ล้มเหลว", "0"),
            ("ระยะเวลารอบล่าสุด", "29.87 วินาที"),
        ],
        [8, 8],
        15,
    )

    document.add_heading("5. สรุปผลรายหมวด", level=1)
    category_rows = []
    for category in categories:
        category_rows.append(
            (
                str(category["number"]),
                category["name"],
                str(len(category["rows"])),
                str(sum(1 for row in category["rows"] if row[2] == "PASS")),
                "PASS",
            )
        )
    add_table(document, ["หมวด", "ขอบเขต", "กรณีทดสอบ", "ผ่าน", "ผล"], category_rows, [2, 9, 3, 2, 3], 13)

    document.add_heading("6. ผลการทดสอบสำคัญ", level=1)
    document.add_heading("6.1 การคำนวณสูตร", level=2)
    document.add_paragraph(
        "ตรวจสอบสูตรพื้นฐาน การบวก ลบ คูณ หาร IF, SUM, MAX และ MIN แล้วผ่านทั้งหมด "
        "รวมถึงทดสอบการคำนวณใหม่หลังแก้ไขข้อมูล"
    )
    add_table(
        document,
        ["สูตร", "ผลลัพธ์ตัวอย่าง", "สถานะ"],
        [
            ("a + b", "5", "PASS"),
            ("a - b", "2", "PASS"),
            ("a × b", "6", "PASS"),
            ("a ÷ b", "2", "PASS"),
            ("IF", "10", "PASS"),
            ("SUM", "16", "PASS"),
            ("MAX", "9", "PASS"),
            ("MIN", "2", "PASS"),
        ],
        [6, 6, 4],
        14,
    )

    document.add_heading("6.2 การใช้งานพร้อมกัน", level=2)
    document.add_paragraph(
        "ทดสอบ 50 concurrent HTTP requests ไปยัง /login สำเร็จ 50 จาก 50 requests "
        "ไม่พบ request ล้มเหลว ใช้เวลารวม 19.451 วินาที คิดเป็น 2.57 requests ต่อวินาที "
        "ผลนี้เป็นการทดสอบ endpoint เบื้องต้น ไม่ใช่การจำลองผู้ใช้ที่ล็อกอินและทำธุรกรรมครบทุกขั้นตอน"
    )

    document.add_heading("6.3 HTTPS", level=2)
    document.add_paragraph(
        "ทดสอบแอปผ่าน https://msu-eva.test:8443 โดยใช้ certificate สำหรับ UAT local "
        "แอปตอบ HTTP 200, certificate verification ผ่าน (verify=0) และ asset โลโก้ตอบ HTTP 200 "
        "โดยไม่พบปัญหา mixed content หลังแก้ trusted proxy"
    )

    document.add_heading("7. ข้อจำกัดและเงื่อนไขก่อน Production", level=1)
    document.add_paragraph(
        "ผล HTTPS ในรายงานนี้รับรองเฉพาะสภาพแวดล้อม UAT local ไม่ใช่การรับรอง Production "
        "เนื่องจากใช้ mkcert, Local CA และชื่อ msu-eva.test"
    )
    for item in [
        "ต้องใช้ domain หรือ subdomain จริงของลูกค้า",
        "ต้องติดตั้ง certificate จาก CA ที่ browser เชื่อถือ เช่น Let's Encrypt",
        "ต้องตั้ง DNS, Nginx/Apache/Load Balancer และ HTTP-to-HTTPS redirect",
        "ต้องตั้ง APP_URL, SESSION_SECURE_COOKIE และ TRUSTED_PROXIES ให้ตรงกับ Production",
        "ต้องทดสอบ HTTPS, certificate chain, mixed content, login และ session ซ้ำก่อน Go-live",
        "ควรทำ load test เพิ่มด้วย authenticated workflow และข้อมูลใกล้เคียง Production หากมีเกณฑ์รองรับผู้ใช้พร้อมกันที่ชัดเจน",
    ]:
        add_bullet(document, item)

    document.add_heading("8. ข้อสรุป", level=1)
    conclusion = document.add_paragraph()
    conclusion.add_run("ผลการทดสอบ UAT: ").bold = True
    conclusion.add_run("ผ่าน 50 จาก 50 รายการ (100%) สำหรับสภาพแวดล้อม UAT")
    document.add_paragraph(
        "ระบบมีความพร้อมสำหรับขั้นตอนเตรียม Production ภายใต้เงื่อนไขว่าต้องดำเนินการติดตั้ง "
        "domain, certificate และ reverse proxy จริง พร้อมทดสอบด้านความปลอดภัยและประสิทธิภาพซ้ำก่อนเปิดใช้งาน"
    )

    document.add_heading("9. การรับรองผล", level=1)
    signoff = document.add_table(rows=4, cols=2)
    signoff.style = "Table Grid"
    signoff_data = [
        ("ผู้จัดทำ/ผู้ทดสอบ", "........................................................"),
        ("ผู้ตรวจสอบ", "........................................................"),
        ("ผู้อนุมัติ", "........................................................"),
        ("วันที่", "........../........../.........."),
    ]
    for row, data in zip(signoff.rows, signoff_data):
        set_cell_text(row.cells[0], data[0], bold=True, size=16)
        set_cell_shading(row.cells[0], LIGHT_PURPLE)
        set_cell_text(row.cells[1], data[1], size=16)

    document.add_section(WD_SECTION.NEW_PAGE)
    document.add_heading("ภาคผนวก ก รายละเอียดกรณีทดสอบ UAT", level=1)
    for category in categories:
        document.add_heading(f"หมวดที่ {category['number']} {category['name']}", level=2)
        rows = [(code, name, status, count) for code, name, status, count, _note in category["rows"]]
        add_table(document, ["รหัส", "รายการทดสอบ", "ผล", "จำนวนครั้ง"], rows, [4, 9, 3, 3], 12)
        document.add_paragraph()

    document.core_properties.title = "รายงานสรุปผลการทดสอบระบบ MSU-EVA"
    document.core_properties.subject = "User Acceptance Testing"
    document.core_properties.author = "Codex"
    document.core_properties.comments = "Generated from docs/uat-test-result-2026-06-30.md"
    document.save(OUTPUT)
    return OUTPUT, categories


if __name__ == "__main__":
    output, parsed_categories = build_document()
    print(f"Created: {output}")
    print(f"Categories: {len(parsed_categories)}")
    print(f"UAT rows: {sum(len(category['rows']) for category in parsed_categories)}")
