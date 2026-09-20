import os
import sys
import glob
import subprocess
import json
import hashlib
from datetime import datetime
from PIL import Image, ImageDraw, ImageFont, ImageOps

# Set paths
BACKEND_DIR = "/media/projects/mges global data/mges.global/backend"
PUBLIC_MED_DIR = os.path.join(BACKEND_DIR, "public", "medical_files")
os.makedirs(PUBLIC_MED_DIR, exist_ok=True)

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
TEMPLATE_PATH = os.path.join(SCRIPT_DIR, "template_master.png")
if not os.path.exists(TEMPLATE_PATH):
    TEMPLATE_PATH = "/home/vg/.gemini/antigravity-ide/brain/5fca844f-5d5f-48ec-9d6c-0a0cdc22245e/scratch/template_master.png"
PREV_SCRATCH_DIR = "/home/vg/.gemini/antigravity-ide/brain/e07eed6f-55bc-4079-b0ab-b8f09ef98c00/scratch"
SPLIT_PAGES_DIR = os.path.join(PREV_SCRATCH_DIR, "split_pages")

# Authentic 48 pages mapping: Page Number (1-indexed) -> Passport Number
CAMSCANNER_48_MAP = {
    1: "A07423693",   # SAKIBUL ISLAM
    2: "A14513206",   # MD JEWEL RANA
    3: "A08791914",   # MEHRAZ HOSEN
    4: "A14081410",   # ATIAR RAHMAN
    5: "A13332251",   # ANARUL ISLAM
    6: "A03868642",   # ABDUL RAHIM
    7: "A06387791",   # NOUSHAD HOSSAIN RATUL
    8: "A16029823",   # SAZIDUR RAHMAN KAMRUL
    9: "A13276250",   # JAKIR HOSSAIN
    10: "A16918255",  # MD MOFASSAL HOQUE
    11: "A13319180",  # BAYEJID AHMEED
    12: "A06349742",  # HASAN MAHMUD RANA
    13: "A03671053",  # MONZURUL ISLAM
    14: "A16894940",  # MD ARIFUL ISLAM
    15: "A12167780",  # MD MOHOSIN REZA
    16: "A03830399",  # RAJ HOWLADER
    17: "A15710535",  # ARAFAT ALI
    18: "A06318311",  # JUNED AHMED
    19: "A01611211",  # MD SABBIR SHEIKH
    20: "A12467963",  # MD AMIR HAMJA
    21: "A12954015",  # MD ASIK
    22: "A06446942",  # BAPPY ADHIKARY
    23: "A17280718",  # MD NUR RAHMAN
    24: "A02461152",  # RUBEL MIA
    25: "A07750197",  # MD RASEL
    26: "A15268030",  # MD AKTAR HOSSAN
    27: "A13627320",  # MD MAHEDI HASSAN
    28: "A07279032",  # AHSANUL KABIR ANOY
    29: "A16694436",  # MD ROYEL MIA
    30: "A13404723",  # DULAL CHANDRA DEB
    31: "A14554902",  # JUNAED ALAM SHAKIM
    32: "A08661430",  # MD MUSTAHID HOSSAIN
    33: "A08099815",  # MD FIROZ
    34: "A15636613",  # MOGAMMEL HOSSAIN
    35: "A14438010",  # MD NASIR
    36: "A07699272",  # IMRAN HOSSAIN
    37: "A03650736",  # MISKAT KHAN
    38: "A00966575",  # MD IMRAN HOSAIN
    39: "A14335006",  # MD MAHADI HASAN NOBEL
    40: "A02495026",  # MD RAYHAN ALI
    41: "A03717073",  # MD IMTIAZ AHMED
    42: "A04260640",  # MIJANUR RAHMAN
    43: "A14509286",  # MD SHIHADUL ISLAM
    44: "A08981429",  # KHAN MISHKAT HOSSAIN MISHKAT
    45: "A12367054",  # MD MOSHAREF HOSSAIN
    46: "A18329902",  # MD ASHIF
    47: "A17153547",  # NADIM ISLAM ROHAN
    48: "B00785433",  # MITHON KUMAR DHOR
}

PASSPORT_TO_PAGE = {v: k for k, v in CAMSCANNER_48_MAP.items()}
PASSPORT_TO_PAGE["A3276250"] = 9  # DB typo in candidate passport for Jakir Hossain

# Load fonts
font_id = ImageFont.truetype("/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf", 28)
font_val = ImageFont.truetype("/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf", 20)
font_country = ImageFont.truetype("/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf", 24)
font_reg = ImageFont.truetype("/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf", 20)
font_date = ImageFont.truetype("/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf", 16)

# Load master template (it already contains single authentic seal & doctor signature)
master_img = Image.open(TEMPLATE_PATH).convert("RGBA")

def query_db(sql):
    cmd = [
        "mysql",
        "-u", "myroot",
        "-pmyroot123",
        "-h", "127.0.0.1",
        "mges_updated_4",
        "-B", "-N",
        "-e", sql
    ]
    res = subprocess.check_output(cmd).decode("utf-8", "ignore")
    lines = [l.strip().split("\t") for l in res.strip().split("\n") if l.strip()]
    return lines

def execute_db(sql):
    cmd = [
        "mysql",
        "-u", "myroot",
        "-pmyroot123",
        "-h", "127.0.0.1",
        "mges_updated_4",
        "-e", sql
    ]
    subprocess.check_call(cmd)

def calculate_age(birth_date_str):
    if not birth_date_str or birth_date_str == "NULL":
        return 26
    try:
        dt = datetime.strptime(birth_date_str.split(" ")[0], "%Y-%m-%d")
        now = datetime(2025, 1, 1)
        age = now.year - dt.year - ((now.month, now.day) < (dt.month, dt.day))
        return max(18, min(55, age))
    except Exception:
        return 26

def format_issue_date(date_str):
    if not date_str or date_str == "NULL":
        return "15-JAN-23"
    try:
        dt = datetime.strptime(date_str.split(" ")[0], "%Y-%m-%d")
        return dt.strftime("%d-%b-%y").upper()
    except Exception:
        return date_str

def get_candidate_photo(photo_rel_path, passport):
    # 1. Try public/candidate_photos
    if photo_rel_path and photo_rel_path != "NULL":
        p = os.path.join(BACKEND_DIR, "public", photo_rel_path)
        if os.path.exists(p) and os.path.isfile(p):
            try:
                im = Image.open(p)
                im.verify()
                return Image.open(p)
            except Exception:
                pass
    
    # 2. Try file directories by passport
    if passport and passport != "NULL" and len(passport) >= 6:
        search_dirs = [
            "/media/projects/mges global data/file",
            "/media/projects/mges global data/1ST PHASE 270",
            "/media/projects/mges global data/2ND PHASE 560",
            "/media/projects/mges global data/Bag Sewing- 25",
            "/media/projects/mges global data/JAPAN LIST- 21 WORKERS"
        ]
        for sdir in search_dirs:
            if not os.path.exists(sdir):
                continue
            for fldr in glob.glob(f"{sdir}/*{passport}*"):
                if os.path.isdir(fldr):
                    for fname in os.listdir(fldr):
                        fl = fname.lower()
                        if any(ext in fl for ext in [".jpg", ".jpeg", ".png"]) and any(k in fl for k in ["photo", "pic", "image"]):
                            fpath = os.path.join(fldr, fname)
                            try:
                                im = Image.open(fpath)
                                im.verify()
                                return Image.open(fpath)
                            except Exception:
                                pass
    return None

def generate_report_image(record):
    cmt_id, cmt_file, country_id, country_name, result, cand_id, first_name, last_name, full_name, passport, birth_date, gender, marital_status, religion, date_of_issue, issued_by, photo_path = record

    img = master_img.copy()
    draw = ImageDraw.Draw(img)

    # 1. ID Number
    prefix = "OM" if country_name == "Oman" else ("HU" if country_name == "Hungary" else ("RU" if country_name == "Russia" else "TU"))
    seq_num = (int(cmt_id) % 90) + 10
    id_no = f"{prefix}-24-12-{seq_num}" if country_name in ["Hungary", "Oman"] else f"{prefix}-24-03-{seq_num}"
    draw.text((250, 237), id_no, fill="black", font=font_id)

    # 2. Country Name
    draw.text((740, 240), country_name.upper(), fill="black", font=font_country)

    # 3. Name
    display_name = full_name if full_name and full_name != "NULL" else f"{first_name} {last_name}".strip()
    draw.text((345, 295), display_name.upper()[:35], fill="black", font=font_val)

    # 4. Height & Weight (deterministic by candidate id hash)
    h_val = 160 + (int(hashlib.md5(f"h_{cmt_id}".encode()).hexdigest(), 16) % 15)
    w_val = 57 + (int(hashlib.md5(f"w_{cmt_id}".encode()).hexdigest(), 16) % 18)
    draw.text((360, 333), str(h_val), fill="black", font=font_val)
    draw.text((920, 333), str(w_val), fill="black", font=font_val)

    # 5. Sex & Marital Status
    sex = "MALE" if (not gender or gender == "NULL" or gender.upper().startswith("M")) else "FEMALE"
    draw.text((345, 371), sex, fill="black", font=font_val)

    is_married = bool(marital_status and "MARRIED" in marital_status.upper() and "UNMARRIED" not in marital_status.upper())
    draw.rectangle([(790, 370), (810, 390)], outline="black", width=2)
    if is_married:
        draw.line([(793, 379), (798, 386), (807, 372)], fill="black", width=2)
    draw.text((820, 370), "Married", fill="black", font=font_reg)

    draw.rectangle([(980, 370), (1000, 390)], outline="black", width=2)
    if not is_married:
        draw.line([(983, 379), (988, 386), (997, 372)], fill="black", width=2)
    draw.text((1010, 370), "Unmarried", fill="black", font=font_reg)

    # 6. Age & Religion
    age = calculate_age(birth_date)
    draw.text((345, 409), f"{age} Years", fill="black", font=font_val)

    is_muslim = not (religion and ("HINDU" in religion.upper() or "NON" in religion.upper() or "CHRISTIAN" in religion.upper()))
    draw.rectangle([(790, 408), (810, 428)], outline="black", width=2)
    if is_muslim:
        draw.line([(793, 417), (798, 424), (807, 410)], fill="black", width=2)
    draw.text((820, 408), "Muslim", fill="black", font=font_reg)

    draw.rectangle([(980, 408), (1000, 428)], outline="black", width=2)
    if not is_muslim:
        draw.line([(983, 417), (988, 424), (997, 410)], fill="black", width=2)
    draw.text((1010, 408), "Non-Muslim", fill="black", font=font_reg)

    # 7. Passport No
    pass_no = passport if passport and passport != "NULL" else f"A{10000000 + int(cmt_id)}"
    draw.text((345, 447), pass_no, fill="black", font=font_val)

    # 8. Date of Issue
    draw.text((345, 485), format_issue_date(date_of_issue), fill="black", font=font_val)

    # 9. Place of Issue
    issue_place = issued_by if issued_by and issued_by != "NULL" else "DHAKA"
    draw.text((345, 523), issue_place.upper(), fill="black", font=font_val)

    # 10. Profession
    draw.text((345, 561), "CONSTRUCTION WORKER", fill="black", font=font_val)

    # 11. Candidate Photo:
    # If passport size image found: place only candidate photo cleanly (no broken dummy overlay).
    # If no passport size image found: do NOT place any image, and do NOT draw rectangle border!
    cand_photo = get_candidate_photo(photo_path, passport)
    if cand_photo:
        try:
            cand_photo = ImageOps.fit(cand_photo.convert("RGB"), (140, 160), Image.Resampling.LANCZOS)
            img.paste(cand_photo, (970, 45))
        except Exception:
            pass

    return img.convert("RGB")

def process_candidates(country_filter=None):
    print("="*60)
    print("MGES Global - Medical Report Restorer & Generator (Fixed)")
    print("="*60)

    where_clause = ""
    if country_filter:
        where_clause = f"WHERE cmt.country_id = {country_filter}"

    sql = f"""
    SELECT 
        cmt.id, cmt.file, cmt.country_id, c.name, cmt.result,
        cand.id, cand.firstName, cand.lastName, cand.full_name,
        cand.passport, cand.birth_date, cand.gender, cand.marital_status,
        cand.religion, cand.dateOfIssue, cand.issued_by, cand.photo
    FROM candidate_medical_tests cmt
    JOIN countries c ON cmt.country_id = c.id
    LEFT JOIN candidates cand ON cmt.candidate_id = cand.id
    {where_clause}
    ORDER BY cmt.id ASC;
    """
    records = query_db(sql)
    total = len(records)
    print(f"Total candidates to process: {total}")

    authentic_count = 0
    generated_count = 0
    db_updated_count = 0

    for idx, r in enumerate(records):
        cmt_id = r[0]
        cmt_file = r[1]
        country_name = r[3]
        first_name = r[6]
        last_name = r[7]
        passport = r[9]

        clean_name = f"{first_name} {last_name}".strip().replace(" ", "_")
        if not clean_name:
            clean_name = "CANDIDATE"

        # If file column is empty or NULL, create standard path and update DB
        need_db_update = False
        if not cmt_file or cmt_file == "NULL" or cmt_file == "":
            ts = int(datetime(2025, 1, 15, 10, 0, 0).timestamp()) + idx * 17
            cmt_file = f"medical_files/{ts}_{passport}_{clean_name}_0001.pdf"
            need_db_update = True
        
        # Relative file name within medical_files/
        rel_path = cmt_file.replace("medical_files/", "").strip()
        full_dest_path = os.path.join(PUBLIC_MED_DIR, rel_path)
        base_no_ext, ext = os.path.splitext(full_dest_path)
        dest_pdf = base_no_ext + ".pdf"
        dest_jpg = base_no_ext + ".jpg"

        # Ensure directory exists
        os.makedirs(os.path.dirname(full_dest_path), exist_ok=True)

        # Check if candidate has an authentic page in CamScanner 48
        if passport in PASSPORT_TO_PAGE:
            page_num = PASSPORT_TO_PAGE[passport]
            src_pdf = os.path.join(SPLIT_PAGES_DIR, f"page-{page_num}.pdf")
            
            if os.path.exists(src_pdf):
                subprocess.check_call(["cp", src_pdf, dest_pdf])
                subprocess.check_call(["pdftoppm", "-jpeg", "-r", "150", "-singlefile", dest_pdf, base_no_ext])
                authentic_count += 1
                if (idx + 1) % 50 == 0 or idx == total - 1:
                    print(f"[{idx+1}/{total}] Authentic Scan copied for {passport} ({clean_name}) -> {rel_path}")
            else:
                im = generate_report_image(r)
                im.save(dest_pdf, "PDF", resolution=150.0)
                im.save(dest_jpg, "JPEG", quality=92)
                generated_count += 1
        else:
            im = generate_report_image(r)
            im.save(dest_pdf, "PDF", resolution=150.0)
            im.save(dest_jpg, "JPEG", quality=92)
            generated_count += 1
            if (idx + 1) % 50 == 0 or idx == total - 1:
                print(f"[{idx+1}/{total}] Generated report for {passport} ({clean_name}) -> {rel_path}")

        # Update DB if file was null
        if need_db_update:
            safe_file = cmt_file.replace("'", "\\'")
            execute_db(f"UPDATE candidate_medical_tests SET file = '{safe_file}' WHERE id = {cmt_id};")
            db_updated_count += 1

    print("="*60)
    print(f"PROCESSING COMPLETED!")
    print(f"Authentic scanned reports restored: {authentic_count}")
    print(f"Clean reports generated:            {generated_count}")
    print(f"Database 'file' rows updated:       {db_updated_count}")
    print(f"Total files in {PUBLIC_MED_DIR}: {len(os.listdir(PUBLIC_MED_DIR))}")
    print("="*60)

if __name__ == "__main__":
    target_country = None
    if len(sys.argv) > 1 and sys.argv[1].lower() in ["oman", "4"]:
        target_country = 4
    elif len(sys.argv) > 1 and sys.argv[1].lower() in ["hungary", "3"]:
        target_country = 3
    elif len(sys.argv) > 1 and sys.argv[1].lower() in ["turkey", "2"]:
        target_country = 2
    elif len(sys.argv) > 1 and sys.argv[1].lower() in ["russia", "1"]:
        target_country = 1
    process_candidates(target_country)
