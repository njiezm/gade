// Génère l'identité GADÉ — Sinéma lakay : logo SVG (texte vectorisé), favicon, PNG, image OG.
// Usage : npm run logo
import opentype from 'opentype.js';
import sharp from 'sharp';
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const P = resolve(dirname(fileURLToPath(import.meta.url)), '../..') + '/';
const load = (f) => { const b = readFileSync(P + f); return opentype.parse(b.buffer.slice(b.byteOffset, b.byteOffset + b.byteLength)); };
const SERIF = load('node_modules/@fontsource/young-serif/files/young-serif-latin-400-normal.woff');
const HAND = load('node_modules/@fontsource/kalam/files/kalam-latin-700-normal.woff');

export const C = {
    nuit: '#160e0b', velours: '#2b1613', bordeaux: '#6e1c27', rouge: '#c8322b',
    jaune: '#f2b53a', vert: '#2f7d4a', creme: '#f7ecd9', sable: '#cdb89a',
};
const f = (n) => (+n.toFixed(2)).toString();
const pathData = (p) => p.commands.map((c) => c.type === 'M' || c.type === 'L' ? `${c.type}${f(c.x)} ${f(c.y)}`
    : c.type === 'Q' ? `Q${f(c.x1)} ${f(c.y1)} ${f(c.x)} ${f(c.y)}`
    : c.type === 'C' ? `C${f(c.x1)} ${f(c.y1)} ${f(c.x2)} ${f(c.y2)} ${f(c.x)} ${f(c.y)}` : 'Z').join('');

function texte(font, str, size, x, y, track = 0) {
    let cx = x; const ds = [];
    for (const ch of str) {
        const g = font.charToGlyph(ch);
        ds.push(pathData(g.getPath(cx, y, size)));
        cx += g.advanceWidth * size / font.unitsPerEm + track * size;
    }
    return { d: ds.join(''), w: cx - x - track * size };
}

// ---------- Le signe : un écran-velours, un œil qui « gadé », une bande de madras ----------
function signe(id = 'g', { madras = true } = {}) {
    const plaid = madras ? `
    <g clip-path="url(#${id}-clip)">
      <rect x="0" y="90" width="120" height="30" fill="${C.rouge}"/>
      <g fill="${C.jaune}" opacity=".9">
        <rect x="10" y="90" width="4" height="30"/><rect x="42" y="90" width="4" height="30"/><rect x="74" y="90" width="4" height="30"/><rect x="106" y="90" width="4" height="30"/>
        <rect x="0" y="97" width="120" height="3"/>
      </g>
      <g fill="${C.vert}" opacity=".85">
        <rect x="24" y="90" width="7" height="30"/><rect x="56" y="90" width="7" height="30"/><rect x="88" y="90" width="7" height="30"/>
        <rect x="0" y="107" width="120" height="5"/>
      </g>
      <rect x="0" y="88" width="120" height="3" fill="${C.nuit}" opacity=".35"/>
    </g>` : '';
    return `
  <defs>
    <clipPath id="${id}-clip"><rect width="120" height="120" rx="28"/></clipPath>
    <radialGradient id="${id}-vel" cx=".35" cy=".25" r=".9"><stop offset="0" stop-color="#8a2632"/><stop offset="1" stop-color="${C.bordeaux}"/></radialGradient>
  </defs>
  <rect width="120" height="120" rx="28" fill="url(#${id}-vel)"/>${plaid}
  <path d="M14 ${madras ? 50 : 60}Q60 ${madras ? 6 : 16} 106 ${madras ? 50 : 60}Q60 ${madras ? 94 : 104} 14 ${madras ? 50 : 60}Z" fill="${C.creme}"/>
  <circle cx="60" cy="${madras ? 50 : 60}" r="21" fill="${C.jaune}"/>
  <path d="M53.5 ${madras ? 39 : 49}v22a2 2 0 0 0 3 1.7l17-11a2 2 0 0 0 0-3.4l-17-11a2 2 0 0 0-3 1.7z" fill="${C.nuit}"/>`;
}

// ---------- Logo horizontal ----------
function logo({ couleur = C.creme, accent = C.jaune, id = 'lg' } = {}) {
    const mot = texte(SERIF, 'GADÉ', 92, 0, 0, 0.04);
    const sig = texte(HAND, 'Sinéma lakay', 34, 0, 0);
    const x = 142;
    const w = Math.ceil(x + Math.max(mot.w, sig.w + 6) + 4);
    const motT = texte(SERIF, 'GADÉ', 92, x, 76, 0.04);
    const sigT = texte(HAND, 'Sinéma lakay', 34, x + 4, 118);
    return {
        w, h: 142,
        svg: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -14 ${w} 142" width="${w}" height="142" role="img" aria-label="GADÉ — Sinéma lakay">
  <g transform="translate(0 4)">${signe(id)}</g>
  <path d="${motT.d}" fill="${couleur}"/>
  <path d="${sigT.d}" fill="${accent}"/>
</svg>`,
    };
}

const out = (f, s) => { mkdirSync(dirname(P + f), { recursive: true }); writeFileSync(P + f, s); };

const sombre = logo();
const clair = logo({ couleur: C.nuit, accent: C.rouge, id: 'lc' });
const mark = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="120" height="120" role="img" aria-label="GADÉ">${signe('m')}</svg>`;
const favicon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120">${signe('f', { madras: false })}</svg>`;

out('public/images/brand/logo-gade.svg', sombre.svg);
out('public/images/brand/logo-gade-clair.svg', clair.svg);
out('public/images/brand/signe-gade.svg', mark);
out('public/images/brand/favicon.svg', favicon);

// Partiel Blade : signe en ligne (identifiants uniques par usage)
out('resources/views/partials/signe.blade.php', `{{-- Généré par resources/brand/logo.mjs --}}
<svg class="{{ $class ?? 'signe' }}" viewBox="0 0 120 120" aria-hidden="true" focusable="false">${signe('{{ $id ?? \'s\' }}')}</svg>
`);
const motHeader = texte(SERIF, 'GADÉ', 92, 0, 88, 0.04);
out('resources/views/partials/mot-gade.blade.php', `{{-- Généré par resources/brand/logo.mjs : « GADÉ » vectorisé (Young Serif) --}}
<svg class="{{ $class ?? 'mot-gade' }}" viewBox="0 0 ${Math.ceil(motHeader.w + 2)} 92" aria-hidden="true" focusable="false"><path d="${motHeader.d}" fill="currentColor"/></svg>
`);

// PNG
const png = (svg, w, file, bg) => sharp(Buffer.from(svg), { density: 300 }).resize({ width: w }).flatten(bg ? { background: bg } : false).png().toFile(P + file);
await png(sombre.svg, 1200, 'docs/logo-gade.png', C.nuit);
await png(clair.svg, 1200, 'docs/logo-gade-clair.png', C.creme);
await png(mark, 512, 'public/images/brand/favicon-512.png');
await png(favicon, 180, 'public/images/brand/favicon-180.png');
await png(favicon, 192, 'public/images/brand/favicon-192.png');

// favicon.ico (PNG 32 et 48 embarqués)
const icoPngs = await Promise.all([32, 48].map((s) => sharp(Buffer.from(favicon), { density: 300 }).resize(s, s).png().toBuffer()));
const header = Buffer.alloc(6 + 16 * icoPngs.length);
header.writeUInt16LE(0, 0); header.writeUInt16LE(1, 2); header.writeUInt16LE(icoPngs.length, 4);
let offset = header.length;
icoPngs.forEach((b, i) => {
    const s = [32, 48][i];
    header.writeUInt8(s, 6 + i * 16); header.writeUInt8(s, 7 + i * 16); header.writeUInt8(0, 8 + i * 16); header.writeUInt8(0, 9 + i * 16);
    header.writeUInt16LE(1, 10 + i * 16); header.writeUInt16LE(32, 12 + i * 16);
    header.writeUInt32LE(b.length, 14 + i * 16); header.writeUInt32LE(offset, 18 + i * 16);
    offset += b.length;
});
writeFileSync(P + 'public/favicon.ico', Buffer.concat([header, ...icoPngs]));

// Image Open Graph 1200×630 : velours, rideau, madras, signature
const accroche = texte(HAND, 'Ka ou lé gadé jodi-a ?', 54, 0, 0);
const sous = 'Films, séries et documentaires en français, choisis avec soin';
const sousT = texte(SERIF, sous, 25, 0, 0);
const lx = (1200 - sombre.w * 1.55) / 2;
const og = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630" width="1200" height="630">
  <defs>
    <radialGradient id="spot" cx=".5" cy=".38" r=".75"><stop offset="0" stop-color="#4a201d"/><stop offset=".55" stop-color="${C.velours}"/><stop offset="1" stop-color="${C.nuit}"/></radialGradient>
    <linearGradient id="pli" x1="0" x2="1"><stop offset="0" stop-color="#000" stop-opacity=".35"/><stop offset=".5" stop-color="#fff" stop-opacity=".06"/><stop offset="1" stop-color="#000" stop-opacity=".35"/></linearGradient>
    <pattern id="rideau" width="60" height="630" patternUnits="userSpaceOnUse"><rect width="60" height="630" fill="url(#pli)"/></pattern>
  </defs>
  <rect width="1200" height="630" fill="url(#spot)"/>
  <rect x="0" width="150" height="630" fill="${C.bordeaux}"/><rect x="0" width="150" height="630" fill="url(#rideau)"/>
  <rect x="1050" width="150" height="630" fill="${C.bordeaux}"/><rect x="1050" width="150" height="630" fill="url(#rideau)"/>
  <g transform="translate(${f(lx)} 150) scale(1.55)">${sombre.svg.replace(/^<svg[^>]*>/, '').replace('</svg>', '')}</g>
  <path d="${texte(HAND, 'Ka ou lé gadé jodi-a ?', 54, (1200 - accroche.w) / 2, 420).d}" fill="${C.jaune}"/>
  <path d="${texte(SERIF, sous, 25, (1200 - sousT.w) / 2, 482).d}" fill="${C.sable}"/>
  <clipPath id="bande"><rect width="900" height="70"/></clipPath>
  <g transform="translate(150 560)" clip-path="url(#bande)">
    <rect width="900" height="70" fill="${C.rouge}"/>
    ${Array.from({ length: 29 }, (_, i) => `<rect x="${i * 32 + 6}" width="5" height="70" fill="${C.jaune}" opacity=".85"/><rect x="${i * 32 + 18}" width="8" height="70" fill="${C.vert}" opacity=".8"/>`).join('')}
    <rect y="18" width="900" height="5" fill="${C.jaune}" opacity=".85"/><rect y="40" width="900" height="8" fill="${C.vert}" opacity=".8"/>
  </g>
</svg>`;
out('resources/brand/og-gade.svg', og);
await sharp(Buffer.from(og), { density: 144 }).resize(1200, 630).jpeg({ quality: 86, mozjpeg: true }).toFile(P + 'public/images/brand/og-gade.jpg');
await sharp(Buffer.from(og), { density: 144 }).resize(1200, 630).png().toFile(P + 'docs/og-gade.png');

console.log('Identité générée :', sombre.w, '×', sombre.h);
