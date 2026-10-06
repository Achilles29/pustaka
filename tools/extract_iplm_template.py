"""Read the supplied OOXML template; emit JSON to stdout (no file writes)."""
import json
import re
import zipfile
import xml.etree.ElementTree as ET
from pathlib import Path

path = Path(__file__).resolve().parents[1] / 'docs/iplm/Template_IPLM_2026.xlsx'
ns = {'s': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
with zipfile.ZipFile(path) as archive:
    strings = [''.join(t.text or '' for t in si.findall('.//s:t', ns))
               for si in ET.fromstring(archive.read('xl/sharedStrings.xml'))]
    relationships = {r.attrib['Id']: r.attrib['Target'] for r in
                     ET.fromstring(archive.read('xl/_rels/workbook.xml.rels'))}
    sheets = {}
    for sheet in ET.fromstring(archive.read('xl/workbook.xml')).find('s:sheets', ns):
        target = relationships[sheet.attrib['{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id']]
        root = ET.fromstring(archive.read(target.lstrip('/') if target.startswith('/') else 'xl/' + target))
        cells = {}
        for cell in root.findall('.//s:sheetData/s:row/s:c', ns):
            value = cell.find('s:v', ns)
            if value is not None:
                cells[cell.attrib['r']] = strings[int(value.text)] if cell.attrib.get('t') == 's' else value.text
        sheets[sheet.attrib['name']] = cells

columns = [chr(65+i) for i in range(26)] + ['A'+chr(65+i) for i in range(17)]
keys = ['library_type_id','library_subtype_id','teachers','students','employees','institution_name','npsn','npp','library_name','address','province','regency','respondent_name','respondent_phone',
        'print_titles','print_copies','digital_titles','digital_copies','added_print_titles','added_print_copies','added_digital_titles','added_digital_copies','budget_bos','budget_non_bos','budget_collection','staff_qualified','staff_other','staff_training','budget_training','literacy_participants','library_users','ict_users','used_print_titles','used_print_copies','used_digital_titles','used_digital_copies','literacy_events','partnerships','service_types','service_policies','regional_policies','budget_management','evidence_url']
definition_rows = list(range(2,17)) + [21,22,23,17,18,19,20] + list(range(24,30))
fields = []
for i, (column, key) in enumerate(zip(columns,keys)):
    label = sheets['IPLM Kab-Kota'].get(column+'2') or sheets['IPLM Kab-Kota'].get(column+'1')
    section = 'identity' if i < 14 else 'collection' if i < 25 else 'staff' if i < 29 else 'service' if i < 36 else 'management' if i < 42 else 'evidence'
    definition = sheets['Definisi'].get('D'+str(definition_rows[i-14]),'') if 14 <= i < 42 else ''
    kind = 'number' if 14 <= i < 42 or key in ['teachers','students','employees'] else 'text'
    if key.startswith('budget_'): kind = 'money'
    if key in ['library_type_id','library_subtype_id','province','regency']: kind = 'select'
    if key == 'evidence_url': kind = 'url'
    evidence = ''
    if 14 <= i <= 21 or key.startswith('used_'): evidence = 'Daftar koleksi dalam format Excel/foto koleksi.'
    if key in ['staff_qualified','staff_other']: evidence = 'Daftar identitas tenaga perpustakaan (Excel), scan/copy ijazah.'
    if key == 'staff_training': evidence = 'Daftar peserta PKB (Excel), scan/foto sertifikat PKB.'
    evidence = {'literacy_participants':'Absensi, foto peserta saat kegiatan berlangsung.', 'library_users':'Daftar pemustaka yang mengunjungi perpustakaan dalam format Excel.', 'ict_users':'Daftar pemustaka yang menggunakan sarana TIK perpustakaan dalam format Excel.', 'literacy_events':'Daftar kegiatan (Excel), foto kegiatan.', 'partnerships':'MoU, foto saat advokasi.', 'service_types':'Daftar layanan (Excel), foto layanan.', 'service_policies':'Daftar dokumen (Excel), foto dokumen.', 'regional_policies':'Daftar dokumen (Excel), foto dokumen.'}.get(key,evidence)
    if key.startswith('budget_'): evidence = 'Belum dirinci pada slide bukti dukung; menunggu ketentuan admin kabupaten.'
    fields.append(dict(code=key, excel_column=column, label=re.sub(r'\s+',' ',label).strip(), definition=definition, kind=kind, section=section, evidence_hint=evidence, sort_order=i+1))
print(json.dumps({'source':path.name,'sheet':'IPLM Kab-Kota','fields':fields,'validation':sheets['Validasi Data']},ensure_ascii=False,indent=2))
