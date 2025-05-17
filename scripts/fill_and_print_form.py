import sys
from reportlab.pdfgen import canvas
from reportlab.lib.pagesizes import A4
from PyPDF2 import PdfReader, PdfWriter
import arabic_reshaper
from bidi.algorithm import get_display
import win32print
import win32api
import os

# Receive CLI arguments
form_path = sys.argv[1]
student_name = sys.argv[2]
academic_year = sys.argv[3]
level = sys.argv[4]
purpose = sys.argv[5]
date = sys.argv[6]
notes = sys.argv[7] if len(sys.argv) > 7 else ''

# Helper: reshape + bidi for Arabic
def prepare_arabic(text):
    return get_display(arabic_reshaper.reshape(text))

# Prepare overlay
c = canvas.Canvas("overlay.pdf", pagesize=A4)
c.setFont("Traditional Arabic", 16)

# Set coordinates manually according to the form layout
c.drawRightString(470, 730, prepare_arabic(student_name))     # الطالب
c.drawRightString(470, 700, prepare_arabic(level))             # الفرقة
c.drawRightString(470, 670, prepare_arabic(academic_year))     # العام الجامعي
c.drawRightString(470, 640, prepare_arabic(purpose))           # الغرض
c.drawRightString(470, 610, prepare_arabic(notes))             # ملاحظات
c.drawRightString(470, 580, prepare_arabic(date))              # التاريخ

c.save()

# Merge overlay with original PDF
reader = PdfReader(form_path)
overlay = PdfReader("overlay.pdf")
writer = PdfWriter()

page = reader.pages[0]
page.merge_page(overlay.pages[0])
writer.add_page(page)

output_path = "/home/site/wwwroot/public/FilledForms/filled_registeration_form.pdf"
with open(output_path, "wb") as f:
    writer.write(f)

# Send to printer
printer_name = win32print.GetDefaultPrinter()
win32api.ShellExecute(
    0,
    "print",
    output_path,
    f'/d:"{printer_name}"',
    ".",
    0
)
