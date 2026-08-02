#!/usr/bin/env python
import argparse
import json
import math
import os
import sys
from io import BytesIO

try:
    import fitz
    from PIL import Image, ImageDraw, ImageFont
except Exception as exc:
    sys.stderr.write(json.dumps({"ok": False, "error": "renderer_dependency_missing", "detail": str(exc)}))
    sys.exit(2)


def load_font(size):
    candidates = [
        r"C:\Windows\Fonts\arial.ttf",
        r"C:\Windows\Fonts\segoeui.ttf",
        "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
    ]
    for path in candidates:
        if os.path.exists(path):
            try:
                return ImageFont.truetype(path, size)
            except Exception:
                pass
    return ImageFont.load_default()


def info(pdf_path):
    with fitz.open(pdf_path) as doc:
        print(json.dumps({"ok": True, "pages": doc.page_count}))


def render(pdf_path, output_path, page_number, watermark, zoom):
    with fitz.open(pdf_path) as doc:
        if page_number < 1 or page_number > doc.page_count:
            raise ValueError("page_out_of_range")

        page = doc.load_page(page_number - 1)
        matrix = fitz.Matrix(zoom, zoom)
        pix = page.get_pixmap(matrix=matrix, alpha=False)
        base = Image.open(BytesIO(pix.tobytes("png"))).convert("RGBA")

    overlay = Image.new("RGBA", base.size, (255, 255, 255, 0))
    tile_width = max(520, base.width // 2)
    tile_height = max(150, base.height // 6)
    tile = Image.new("RGBA", (tile_width, tile_height), (255, 255, 255, 0))
    draw_tile = ImageDraw.Draw(tile)
    font = load_font(max(22, int(base.width / 34)))
    text = (watermark or "Pustaka Digital Rembang").strip()[:180]
    bbox = draw_tile.textbbox((0, 0), text, font=font)
    text_width = bbox[2] - bbox[0]
    text_height = bbox[3] - bbox[1]
    draw_tile.text(
        ((tile_width - text_width) / 2, (tile_height - text_height) / 2),
        text,
        fill=(7, 38, 91, 56),
        font=font,
    )
    rotated = tile.rotate(-28, expand=True)

    for y in range(-rotated.height, base.height + rotated.height, max(150, rotated.height)):
        for x in range(-rotated.width, base.width + rotated.width, max(320, int(rotated.width * 0.82))):
            overlay.alpha_composite(rotated, (x, y))

    merged = Image.alpha_composite(base, overlay).convert("RGB")
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    merged.save(output_path, "PNG", optimize=True)
    print(json.dumps({"ok": True, "width": merged.width, "height": merged.height, "page": page_number}))


def main():
    parser = argparse.ArgumentParser(description="Render one PDF page to a watermarked PNG.")
    parser.add_argument("mode", choices=["info", "render"])
    parser.add_argument("--input", required=True)
    parser.add_argument("--output")
    parser.add_argument("--page", type=int, default=1)
    parser.add_argument("--watermark", default="")
    parser.add_argument("--zoom", type=float, default=1.65)
    args = parser.parse_args()

    pdf_path = os.path.abspath(args.input)
    if not os.path.isfile(pdf_path):
        raise FileNotFoundError("pdf_not_found")

    if args.mode == "info":
        info(pdf_path)
        return

    if not args.output:
        raise ValueError("output_required")
    render(pdf_path, os.path.abspath(args.output), args.page, args.watermark, max(1.0, min(2.5, args.zoom)))


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        sys.stderr.write(json.dumps({"ok": False, "error": str(exc)}))
        sys.exit(1)
