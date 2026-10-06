"""Extract only school-master sheets; never personnel sheets from Dapodik."""
import json, pathlib, zipfile, xml.etree.ElementTree as ET, hashlib, os
ROOT=pathlib.Path(__file__).resolve().parents[1]
NS={'m':'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
def rows(filename,header_row):
    with zipfile.ZipFile(ROOT/'docs/iplm'/filename) as z:
        shared=[]
        if 'xl/sharedStrings.xml' in z.namelist():
            shared=[''.join(t.text or '' for t in s.findall('.//m:t',NS)) for s in ET.fromstring(z.read('xl/sharedStrings.xml'))]
        result=[]; headers={}
        for row in ET.fromstring(z.read('xl/worksheets/sheet1.xml')).find('m:sheetData',NS):
            values={}
            for c in row:
                col=''.join(x for x in c.get('r') if x.isalpha())
                if c.find('m:f',NS) is not None: raise ValueError('Source formulas require review')
                v=c.find('m:v',NS); v=v.text if v is not None else ''
                if c.get('t')=='s': v=shared[int(v)]
                elif c.get('t')=='inlineStr': v=''.join(t.text or '' for t in c.findall('.//m:t',NS))
                values[col]=str(v or '').strip()
            num=int(row.get('r'))
            if num==header_row: headers=values
            elif num>header_row and any(values.values()):
                item={name:values.get(col,'') for col,name in headers.items()}
                # Personnel identifiers are irrelevant to this task.
                item.pop('NIP Kepala Sekolah',None);item.pop('Kepala Sekolah',None)
                item['_row']=num;result.append(item)
        return result
if __name__=='__main__':
    files={'initial':('dapodik_awal.xlsx',4),'update':('sekolah_kabupaten_rembang.xlsx',1),'backup':('Rekap_new.xlsx',1)}
    data={key:rows(*spec) for key,spec in files.items()}
    data['hashes']={spec[0]:hashlib.sha256((ROOT/'docs/iplm'/spec[0]).read_bytes()).hexdigest() for spec in files.values()}
    p=ROOT/'docs/iplm/sumber-task7.json';p.write_text(json.dumps(data,ensure_ascii=False,indent=2));os.chmod(p,0o600)
    print({key:len(data[key]) for key in files})
