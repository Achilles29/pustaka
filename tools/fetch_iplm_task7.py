"""Public official school references; bounded requests, cache and explicit failures."""
import concurrent.futures, datetime, html, json, pathlib, re, time, urllib.request, os
ROOT=pathlib.Path(__file__).resolve().parents[1]
DEST=ROOT/'docs/iplm/verifikasi-portal-task7.json'
def scrape(item):
    code=item['source']['code'];url='https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/'+code
    result={'code':code,'url':url,'retrieved_at':datetime.datetime.now(datetime.timezone.utc).isoformat()}
    try:
        req=urllib.request.Request(url,headers={'User-Agent':'Mozilla/5.0 (compatible; LibraryRegistryVerification/1.0)'})
        with urllib.request.urlopen(req,timeout=25) as resp: page=resp.read(1500000).decode('utf-8','replace')
        fields={}
        for row in re.findall(r'<tr\b[^>]*>(.*?)</tr>',page,re.S|re.I):
            cells=[html.unescape(re.sub('<[^>]*>',' ',c)).strip() for c in re.findall(r'<td\b[^>]*>(.*?)</td>',row,re.S|re.I)]
            cells=[' '.join(c.split()) for c in cells]
            if len(cells)>=4 and cells[2]==':':fields[cells[1]]=cells[3]
        if fields.get('NPSN')!=code:raise ValueError('No matching official NPSN in response')
        selected=['Nama','NPSN','Alamat','Desa/Kelurahan','Kecamatan/Kota (LN)','Kab.-Kota/Negara (LN)','Status Sekolah','Bentuk Pendidikan']
        result['fields']={k:fields.get(k,'') for k in selected}
        for label,key in [('Lintang','latitude'),('Bujur','longitude')]:
            m=re.search(label+r':\s*(-?\d+(?:\.\d+)?)',page)
            if m:result[key]=m.group(1)
        result['status']='ok'
    except Exception as e:result.update(status='unavailable',error=str(e)[:160])
    time.sleep(.15);return code,result
if __name__=='__main__':
    plan=json.load(open('/tmp/task7-plan.json'));cache=json.load(open(DEST)) if DEST.exists() else {}
    targets=[r for r in plan if r['status']=='IMPORT' or r['library_id'] in [1624,1598,1644,1632,1586] or not r['source']['gps_valid']]
    targets=[r for r in targets if r['source']['code'] not in cache]
    print('Fetching',len(targets),flush=True)
    with concurrent.futures.ThreadPoolExecutor(max_workers=4) as pool:
        for i,(code,value) in enumerate(pool.map(scrape,targets),1):
            cache[code]=value
            if i%40==0:
                DEST.write_text(json.dumps(cache,ensure_ascii=False,indent=2));os.chmod(DEST,0o600);print('Processed',i,flush=True)
    DEST.write_text(json.dumps(cache,ensure_ascii=False,indent=2));os.chmod(DEST,0o600)
    print('Done',len(cache),'available',sum(r['status']=='ok' for r in cache.values()),flush=True)
