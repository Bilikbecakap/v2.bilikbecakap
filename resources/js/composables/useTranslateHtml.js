// Terjemahkan konten HTML (Quill) per paragraf lewat endpoint penerjemah.
// API penerjemah membatasi panjang output, jadi teks panjang harus dipecah.

const MAX_CHUNK_CHARS = 1200;
const CONCURRENCY = 3;

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || "";

export const translateText = async (text, direction) => {
  const response = await fetch(route("penerjemah.process"), {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      "X-CSRF-TOKEN": csrfToken(),
    },
    body: JSON.stringify({ text, direction }),
  });

  const data = await response.json();
  if (!data.success) {
    throw new Error(data.message || "Gagal menerjemahkan");
  }
  return data.data.translation;
};

// Pecah paragraf panjang per kalimat agar tiap request tetap pendek
const splitLongText = (text) => {
  if (text.length <= MAX_CHUNK_CHARS) return [text];

  const sentences = text.match(/[^.!?]+[.!?]+["')\]]*\s*|[^.!?]+$/g) || [text];
  const chunks = [];
  let current = "";
  for (const sentence of sentences) {
    if (current && (current + sentence).length > MAX_CHUNK_CHARS) {
      chunks.push(current.trim());
      current = "";
    }
    current += sentence;
  }
  if (current.trim()) chunks.push(current.trim());
  return chunks;
};

const escapeHtml = (text) =>
  text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

export const translateHtml = async (html, direction, onProgress = () => {}) => {
  const root = document.createElement("div");
  root.innerHTML = html;

  // Kumpulkan blok teks: elemen top-level, atau <li> untuk list
  const blocks = [];
  for (const el of Array.from(root.children)) {
    const items = ["OL", "UL"].includes(el.tagName) ? Array.from(el.children) : [el];
    for (const item of items) {
      const text = item.textContent.trim();
      if (text) blocks.push({ el: item, chunks: splitLongText(text), result: [] });
    }
  }

  // Konten tanpa elemen blok (teks polos)
  if (!blocks.length && root.textContent.trim()) {
    const p = document.createElement("p");
    p.textContent = root.textContent.trim();
    root.replaceChildren(p);
    blocks.push({ el: p, chunks: splitLongText(p.textContent), result: [] });
  }

  const jobs = blocks.flatMap((block) => block.chunks.map((chunk, i) => ({ block, chunk, i })));
  let done = 0;
  let next = 0;
  onProgress(0, jobs.length);

  const worker = async () => {
    while (next < jobs.length) {
      const job = jobs[next++];
      job.block.result[job.i] = await translateText(job.chunk, direction);
      onProgress(++done, jobs.length);
    }
  };
  await Promise.all(Array.from({ length: Math.min(CONCURRENCY, jobs.length) }, worker));

  // Ganti isi tiap blok dengan terjemahannya, tag (p/h1/li/...) tetap
  for (const block of blocks) {
    block.el.innerHTML = escapeHtml(block.result.join(" "));
  }
  return root.innerHTML;
};
