import os
from PIL import Image, ImageDraw, ImageFont

base_path = "/home/vg/.gemini/antigravity-ide/brain/e07eed6f-55bc-4079-b0ab-b8f09ef98c00/scratch/camscanner_samples/page-01.png"
out_path = "/home/vg/.gemini/antigravity-ide/brain/5fca844f-5d5f-48ec-9d6c-0a0cdc22245e/scratch/template_master.png"

base = Image.open(base_path).convert("RGBA")
draw = ImageDraw.Draw(base)

white = (255, 255, 255, 255)

# 1. Clear ID No area inside rounded rect (x: 230 to 510, y: 230 to 275)
draw.rectangle([(230, 230), (510, 275)], fill=white)

# 2. Clear Country Name value (x: 735 to 895, y: 230 to 275) so we can place HUNGARY, RUSSIA, or TURKEY completely cleanly
draw.rectangle([(735, 230), (895, 275)], fill=white)

# 3. Clear candidate photo area (965, 40, 1150, 215)
draw.rectangle([(965, 40), (1150, 215)], fill=white)

# 4. Clean Personal Information table values
# Row 1: Name value
draw.rectangle([(335, 288), (1155, 323)], fill=white)

# Row 2: Height value & Weight value
draw.rectangle([(335, 327), (470, 362)], fill=white)
draw.rectangle([(755, 327), (1020, 362)], fill=white)

# Row 3: Sex & Status
draw.rectangle([(335, 365), (575, 400)], fill=white)
draw.rectangle([(755, 365), (1155, 400)], fill=white)

# Row 4: Age & Religion
draw.rectangle([(335, 403), (575, 438)], fill=white)
draw.rectangle([(755, 403), (1155, 438)], fill=white)

# Row 5: Passport No & Slip No
draw.rectangle([(335, 441), (575, 476)], fill=white)
draw.rectangle([(755, 441), (1155, 476)], fill=white)

# Row 6: Date of Issue & Visa No
draw.rectangle([(335, 479), (575, 514)], fill=white)
draw.rectangle([(755, 479), (1155, 514)], fill=white)

# Row 7: Place of Issue & Visa Date
draw.rectangle([(335, 517), (575, 552)], fill=white)
draw.rectangle([(755, 517), (1155, 552)], fill=white)

# Row 8: Profession & Agency
draw.rectangle([(335, 555), (575, 595)], fill=white)
draw.rectangle([(755, 555), (1155, 595)], fill=white)

# Clean dividers
line_color = (0, 0, 0, 220)
draw.line([(330, 285), (330, 595)], fill=line_color, width=2)
draw.line([(580, 325), (580, 595)], fill=line_color, width=2)
draw.line([(750, 325), (750, 595)], fill=line_color, width=2)

for y in [325, 363, 401, 439, 477, 515, 553, 595]:
    draw.line([(20, y), (1160, y)], fill=line_color, width=2)

font_lbl = ImageFont.truetype("/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf", 20)
for y in [333, 371, 409, 447, 485, 523, 561]:
    draw.text((755, y), ":", fill="black", font=font_lbl)

base.save(out_path)
print("Updated template_master.png successfully at:", out_path)
