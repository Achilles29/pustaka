<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Small, bounded-memory OOXML writer. Strings are never interpreted as formulas. */
class Catalog_xlsx
{
	public function build($sheet_name, array $headers, iterable $rows)
	{
		if (!class_exists('ZipArchive') || !class_exists('XMLWriter')) throw new RuntimeException('Ekstensi ZIP dan XMLWriter diperlukan untuk Excel.');
		$sheet_path = tempnam(sys_get_temp_dir(), 'pustaka_sheet_');
		$path = tempnam(sys_get_temp_dir(), 'pustaka_xlsx_');
		$zip = new ZipArchive(); $opened = false; $success = false;
		try {
			if (!$sheet_path || !$path) throw new RuntimeException('Berkas sementara Excel tidak dapat dibuat.');
			$xml = new XMLWriter();
			if (!$xml->openURI($sheet_path)) throw new RuntimeException('Lembar Excel tidak dapat ditulis.');
			$xml->startDocument('1.0', 'UTF-8', 'yes');
			$xml->startElement('worksheet'); $xml->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
			$xml->startElement('sheetViews'); $xml->startElement('sheetView'); $xml->writeAttribute('workbookViewId','0');
			$xml->startElement('pane');
			foreach (['ySplit'=>'1','topLeftCell'=>'A2','activePane'=>'bottomLeft','state'=>'frozen'] as $key=>$value) $xml->writeAttribute($key,$value);
			$xml->endElement(); $xml->endElement(); $xml->endElement();
			$xml->startElement('cols');
			foreach ($headers as $index=>$header) {
				$xml->startElement('col'); $xml->writeAttribute('min',(string)($index+1)); $xml->writeAttribute('max',(string)($index+1));
				$xml->writeAttribute('width',(string)max(16,min(42,mb_strlen($header)+3))); $xml->writeAttribute('customWidth','1'); $xml->endElement();
			}
			$xml->endElement(); $xml->startElement('sheetData');
			$number = 1; $this->row($xml, $number, $headers, true);
			foreach ($rows as $row) {
				if (++$number > 1048576) throw new RuntimeException('Batas baris Excel terlampaui. Persempit filter katalog.');
				$this->row($xml, $number, $row, false);
				if ($number % 250 === 0) $xml->flush();
			}
			$xml->endElement(); $xml->startElement('autoFilter');
			$xml->writeAttribute('ref','A1:'.$this->column(count($headers)-1).$number); $xml->endElement();
			$xml->endElement(); $xml->endDocument(); $xml->flush(); unset($xml);
			if ($zip->open($path,ZipArchive::OVERWRITE)!==true) throw new RuntimeException('Arsip Excel tidak dapat dibuat.');
			$opened = true;
			$package = 'http://schemas.openxmlformats.org/package/2006/relationships';
			$office = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
			$main = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
			$parts = [
				'[Content_Types].xml'=>'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
				'_rels/.rels'=>'<Relationships xmlns="'.$package.'"><Relationship Id="rId1" Type="'.$office.'/officeDocument" Target="xl/workbook.xml"/></Relationships>',
				'xl/workbook.xml'=>'<workbook xmlns="'.$main.'" xmlns:r="'.$office.'"><sheets><sheet name="'.htmlspecialchars($sheet_name,ENT_XML1|ENT_QUOTES,'UTF-8').'" sheetId="1" r:id="rId1"/></sheets></workbook>',
				'xl/_rels/workbook.xml.rels'=>'<Relationships xmlns="'.$package.'"><Relationship Id="rId1" Type="'.$office.'/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="'.$office.'/styles" Target="styles.xml"/></Relationships>',
				'xl/styles.xml'=>'<styleSheet xmlns="'.$main.'"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF145B59"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment wrapText="1" vertical="center"/></xf></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>',
			];
			foreach ($parts as $name=>$content) if (!$zip->addFromString($name,'<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.$content)) throw new RuntimeException('Komponen Excel tidak dapat ditulis.');
			if (!$zip->addFile($sheet_path,'xl/worksheets/sheet1.xml')) throw new RuntimeException('Lembar Excel tidak dapat diarsipkan.');
			if (!$zip->close()) throw new RuntimeException('Arsip Excel tidak dapat diselesaikan.');
			$opened = false; $success = true; return $path;
		} finally {
			if ($opened) $zip->close();
			if ($sheet_path && is_file($sheet_path)) unlink($sheet_path);
			if (!$success && $path && is_file($path)) unlink($path);
		}
	}

	private function row(XMLWriter $xml, $number, array $row, $header)
	{
		$xml->startElement('row'); $xml->writeAttribute('r',(string)$number);
		if ($header) { $xml->writeAttribute('ht','32'); $xml->writeAttribute('customHeight','1'); }
		foreach (array_values($row) as $index=>$value) {
			$xml->startElement('c'); $xml->writeAttribute('r',$this->column($index).$number);
			if ($header) $xml->writeAttribute('s','1');
			if (is_int($value) || (is_float($value) && is_finite($value))) {
				$xml->writeElement('v',(string)$value);
			} else {
				// Preserve leading zeros, long ISBNs, and formula-looking text exactly.
				$text = mb_convert_encoding((string)($value ?? ''),'UTF-8','UTF-8');
				$text = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u','',$text);
				$text = mb_convert_encoding(mb_strcut(mb_convert_encoding($text,'UTF-16LE','UTF-8'),0,65534,'UTF-16LE'),'UTF-8','UTF-16LE');
				$xml->writeAttribute('t','inlineStr'); $xml->startElement('is'); $xml->startElement('t');
				$xml->writeAttribute('xml:space','preserve'); $xml->text($text); $xml->endElement(); $xml->endElement();
			}
			$xml->endElement();
		}
		$xml->endElement();
	}

	private function column($index)
	{
		$out = ''; for ($n=$index+1; $n>0; $n=intdiv($n-1,26)) $out=chr(65+(($n-1)%26)).$out;
		return $out;
	}
}
