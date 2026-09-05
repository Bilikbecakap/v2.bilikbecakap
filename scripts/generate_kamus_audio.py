"""
Generate audio kamus massal memakai suara neural Microsoft Edge TTS.
Dipanggil oleh App\\Jobs\\GenerateKamusAudioJob (Laravel) lewat subprocess,
bukan dijalankan manual.

Input : CSV tanpa header, tiap baris "id,kata"
Output: <output_dir>/<id>.mp3

Progress dilaporkan real-time lewat stdout, satu baris JSON per kata selesai:
    {"id": 123, "kata": "apel", "status": "success"}
status salah satu dari: "success" | "skipped" (file sudah ada) | "failed: <pesan>"
"""

import argparse
import asyncio
import csv
import json
from pathlib import Path

import edge_tts


def load_rows(path: str) -> list[tuple[int, str]]:
    rows = []
    with open(path, "r", encoding="utf-8", newline="") as f:
        for row in csv.reader(f):
            if len(row) < 2:
                continue
            kamus_id, kata = row[0].strip(), row[1].strip()
            if kamus_id and kata:
                rows.append((int(kamus_id), kata))
    return rows


async def generate_one(
    kamus_id: int,
    kata: str,
    output_path: Path,
    voice: str,
    rate: str,
    pitch: str,
    semaphore: asyncio.Semaphore,
) -> tuple[int, str, str]:
    async with semaphore:
        if output_path.exists():
            return (kamus_id, kata, "skipped")
        try:
            communicate = edge_tts.Communicate(kata, voice, rate=rate, pitch=pitch)
            await communicate.save(str(output_path))
            return (kamus_id, kata, "success")
        except Exception as e:
            return (kamus_id, kata, f"failed: {e}")


async def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--input", required=True, help="CSV id,kata")
    parser.add_argument("--output", required=True, help="Folder output audio")
    parser.add_argument("--voice", default="id-ID-ArdiNeural")
    parser.add_argument("--rate", default="+0%")
    parser.add_argument("--pitch", default="+0Hz")
    parser.add_argument("--concurrency", type=int, default=5)
    args = parser.parse_args()

    output_dir = Path(args.output)
    output_dir.mkdir(parents=True, exist_ok=True)

    rows = load_rows(args.input)
    if not rows:
        return

    semaphore = asyncio.Semaphore(args.concurrency)
    tasks = [
        generate_one(
            kamus_id,
            kata,
            output_dir / f"{kamus_id}.mp3",
            args.voice,
            args.rate,
            args.pitch,
            semaphore,
        )
        for kamus_id, kata in rows
    ]

    for coro in asyncio.as_completed(tasks):
        kamus_id, kata, status = await coro
        print(json.dumps({"id": kamus_id, "kata": kata, "status": status}), flush=True)


if __name__ == "__main__":
    asyncio.run(main())
