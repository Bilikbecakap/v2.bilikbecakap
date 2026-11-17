// Konversi markdown sederhana ke HTML untuk ditempel ke Quill editor.

const escapeHtml = (text) =>
  text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

const inline = (text) => {
  // Simpan inline code dulu agar isinya tidak ikut diformat
  const codes = [];
  let out = escapeHtml(text).replace(/`([^`]+)`/g, (_, code) => {
    codes.push(code);
    return `\u0000${codes.length - 1}\u0000`;
  });

  out = out
    .replace(/!\[([^\]]*)\]\(([^)\s]+)[^)]*\)/g, '<img src="$2" alt="$1">')
    .replace(/\[([^\]]+)\]\(([^)\s]+)[^)]*\)/g, '<a href="$2">$1</a>')
    .replace(/(\*\*\*|___)(?=\S)(.+?)(?<=\S)\1/g, "<strong><em>$2</em></strong>")
    .replace(/(\*\*|__)(?=\S)(.+?)(?<=\S)\1/g, "<strong>$2</strong>")
    .replace(/(^|[^*\w])\*(?=\S)([^*]+?)(?<=\S)\*(?!\*)/g, "$1<em>$2</em>")
    .replace(/(^|[^_\w])_(?=\S)([^_]+?)(?<=\S)_(?![_\w])/g, "$1<em>$2</em>")
    .replace(/~~(?=\S)(.+?)(?<=\S)~~/g, "<s>$1</s>");

  return out.replace(/\u0000(\d+)\u0000/g, (_, i) => `<code>${codes[i]}</code>`);
};

const LIST_ITEM = /^\s*([-*+]|\d+[.)])\s+(.*)$/;

export const looksLikeMarkdown = (text) =>
  /^\s{0,3}(#{1,6}\s|[-*+]\s|\d+[.)]\s|>\s?|```|(\*\s*){3,}$|(-\s*){3,}$)/m.test(text) ||
  /\*\*[^*\n]+\*\*|__[^_\n]+__|\[[^\]\n]+\]\([^)\n]+\)|`[^`\n]+`/.test(text);

export const markdownToHtml = (markdown) => {
  const lines = markdown.replace(/\r\n?/g, "\n").split("\n");
  const html = [];
  let paragraph = [];

  const flushParagraph = () => {
    if (paragraph.length) html.push(`<p>${inline(paragraph.join(" "))}</p>`);
    paragraph = [];
  };

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];

    // Blok kode
    if (/^\s*```/.test(line)) {
      flushParagraph();
      const code = [];
      while (++i < lines.length && !/^\s*```/.test(lines[i])) code.push(lines[i]);
      html.push(`<pre>${escapeHtml(code.join("\n"))}</pre>`);
      continue;
    }

    if (!line.trim()) {
      flushParagraph();
      continue;
    }

    const heading = line.match(/^\s{0,3}(#{1,6})\s+(.*?)\s*#*\s*$/);
    if (heading) {
      flushParagraph();
      const level = heading[1].length;
      html.push(`<h${level}>${inline(heading[2])}</h${level}>`);
      continue;
    }

    if (/^\s{0,3}((\*\s*){3,}|(-\s*){3,}|(_\s*){3,})$/.test(line)) {
      flushParagraph();
      html.push("<hr>");
      continue;
    }

    if (/^\s{0,3}>/.test(line)) {
      flushParagraph();
      const quote = [];
      while (i < lines.length && /^\s{0,3}>/.test(lines[i])) {
        quote.push(lines[i].replace(/^\s{0,3}>\s?/, ""));
        i++;
      }
      i--;
      html.push(`<blockquote>${inline(quote.join(" "))}</blockquote>`);
      continue;
    }

    if (LIST_ITEM.test(line)) {
      flushParagraph();
      const ordered = /^\s*\d/.test(line);
      const items = [];
      while (i < lines.length && LIST_ITEM.test(lines[i]) && /^\s*\d/.test(lines[i]) === ordered) {
        items.push(`<li>${inline(lines[i].match(LIST_ITEM)[2])}</li>`);
        i++;
      }
      i--;
      const tag = ordered ? "ol" : "ul";
      html.push(`<${tag}>${items.join("")}</${tag}>`);
      continue;
    }

    paragraph.push(line.trim());
  }

  flushParagraph();
  return html.join("");
};
