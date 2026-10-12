// GADÉ — galerie d'avatars des profils (dessins SVG originaux, NJIEZM.FR).
// Style commun : carré 200×200 à fond uni (affiché en rond), halo clair, personnage cerné de brun nuit,
// grands yeux brillants, joues roses. Génère public/images/avatars/*.svg + avatars.json (+ aperçu PNG).
//   npm run avatars
import { mkdirSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const ici = dirname(fileURLToPath(import.meta.url));
const sortie = resolve(ici, '../../public/images/avatars');
const O = '#2a1712'; // trait brun nuit
const T = `stroke="${O}" stroke-width="5" stroke-linejoin="round" stroke-linecap="round"`;
const t = (w) => `stroke="${O}" stroke-width="${w}" stroke-linejoin="round" stroke-linecap="round"`;

// ---------------- Pièces communes ----------------
const oeil = (x, y, r = 13, dx = 1.5, dy = 1.5) =>
    `<circle cx="${x}" cy="${y}" r="${r}" fill="#fff" ${t(4)}/>` +
    `<circle cx="${x + dx}" cy="${y + dy}" r="${(r * 0.52).toFixed(1)}" fill="${O}"/>` +
    `<circle cx="${(x + dx + r * 0.2).toFixed(1)}" cy="${(y + dy - r * 0.22).toFixed(1)}" r="${(r * 0.18).toFixed(1)}" fill="#fff"/>`;
const yeux = (x1, x2, y, r = 13, dx = 1.5, dy = 1.5) => oeil(x1, y, r, dx, dy) + oeil(x2, y, r, dx, dy);
const oeilRieur = (x, y, r = 11) => `<path d="M${x - r} ${y + 3} Q${x} ${y - r} ${x + r} ${y + 3}" fill="none" ${t(5)}/>`;
const sourcil = (x1, y1, x2, y2) => `<path d="M${x1} ${y1} L${x2} ${y2}" fill="none" ${t(5)}/>`;
const joues = (x1, x2, y, rx = 9, ry = 6) =>
    `<ellipse cx="${x1}" cy="${y}" rx="${rx}" ry="${ry}" fill="#ff6f86" opacity=".45"/><ellipse cx="${x2}" cy="${y}" rx="${rx}" ry="${ry}" fill="#ff6f86" opacity=".45"/>`;
const sourire = (x, y, w = 16) => `<path d="M${x - w} ${y} Q${x} ${y + w * 0.9} ${x + w} ${y}" fill="none" ${t(5)}/>`;
const bouche = (id, x, y, w = 18, { langue = true, dents = false } = {}) => {
    const d = `M${x - w} ${y} Q${x} ${y + w * 1.5} ${x + w} ${y} Z`;
    return `<clipPath id="${id}-b"><path d="${d}"/></clipPath>` +
        `<path d="${d}" fill="#5a1620"/>` +
        `<g clip-path="url(#${id}-b)">` +
        (dents ? `<rect x="${x - w}" y="${y - 2}" width="${w * 2}" height="${w * 0.32 + 2}" fill="#fff"/>` : '') +
        (langue ? `<ellipse cx="${x}" cy="${y + w * 0.78}" rx="${w * 0.55}" ry="${w * 0.36}" fill="#f2788a"/>` : '') +
        `</g><path d="${d}" fill="none" ${t(5)}/>`;
};
const boucheO = (x, y, r = 7) => `<ellipse cx="${x}" cy="${y}" rx="${r * 0.85}" ry="${r}" fill="#5a1620" ${t(4)}/>`;
const halo = `<circle cx="100" cy="108" r="82" fill="#fff" opacity=".2"/>`;
const svg = (fond, corps) =>
    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200"><rect width="200" height="200" fill="${fond}"/>${halo}${corps}</svg>\n`;

// ---------------- Les avatars ----------------
const A = {};

// ===== Bèt péyi : animaux des Antilles =====
A.manicou = { nom: 'Manicou', theme: 'bet', svg: svg('#f6c35b', `
    <circle cx="54" cy="64" r="22" fill="#3b3330" ${T}/><circle cx="54" cy="64" r="11" fill="#f2a7b0"/>
    <circle cx="146" cy="64" r="22" fill="#3b3330" ${T}/><circle cx="146" cy="64" r="11" fill="#f2a7b0"/>
    <ellipse cx="100" cy="114" rx="62" ry="54" fill="#dcd6cd" ${T}/>
    <ellipse cx="74" cy="104" rx="19" ry="17" fill="#6b625c"/><ellipse cx="126" cy="104" rx="19" ry="17" fill="#6b625c"/>
    <ellipse cx="100" cy="142" rx="32" ry="22" fill="#f7f2ea" ${t(4)}/>
    ${yeux(74, 126, 104, 12)}
    <ellipse cx="100" cy="130" rx="11" ry="8" fill="#f08aa0" ${t(4)}/>
    <path d="M100 138 v6" ${t(4)}/>${sourire(100, 146, 12)}
    <path d="M60 136 L34 132 M60 144 L34 148 M140 136 L166 132 M140 144 L166 148" ${t(3)}/>
    ${joues(64, 136, 128)}`) };

A.colibri = { nom: 'Colibri', theme: 'bet', svg: svg('#bfe6d9', `
    <ellipse cx="152" cy="80" rx="38" ry="15" transform="rotate(-38 152 80)" fill="#1f7f60" ${T}/>
    <path d="M96 52 q6 -22 22 -26 q-6 12 -2 22 q8 -14 22 -12 q-12 8 -12 20" fill="#2fa37a" ${T}/>
    <circle cx="105" cy="112" r="54" fill="#2fa37a" ${T}/>
    <ellipse cx="112" cy="140" rx="34" ry="24" fill="#8fd6b5"/>
    <path d="M86 132 q20 16 44 2 q-6 18 -22 20 q-16 0 -22 -22z" fill="#e0476a"/>
    <path d="M60 104 L12 98 L60 118 Z" fill="#3a2a22" ${t(4)}/>
    ${yeux(84, 122, 100, 13, 2, 1)}${joues(78, 134, 122)}
    <g transform="translate(26 152)"><circle r="7" cx="-9" fill="#f7942b" ${t(3)}/><circle r="7" cx="9" fill="#f7942b" ${t(3)}/><circle r="7" cy="-9" fill="#f7942b" ${t(3)}/><circle r="7" cy="9" fill="#f7942b" ${t(3)}/><circle r="5" fill="#f2d03a" ${t(3)}/></g>`) };

A.iguane = { nom: 'Iguane', theme: 'bet', svg: svg('#f7c9a8', `
    <path d="M54 82 L62 52 L74 74 L84 42 L96 70 L106 38 L116 68 L128 44 L134 72 L146 56 L148 84 Z" fill="#f2b53a" ${T}/>
    <ellipse cx="100" cy="164" rx="28" ry="20" fill="#f08a4b" ${T}/>
    <ellipse cx="100" cy="116" rx="66" ry="50" fill="#5fb06a" ${T}/>
    <circle cx="62" cy="136" r="5" fill="#4a9a58"/><circle cx="140" cy="138" r="5" fill="#4a9a58"/><circle cx="150" cy="122" r="4" fill="#4a9a58"/><circle cx="52" cy="120" r="4" fill="#4a9a58"/>
    ${yeux(72, 128, 102, 16, 2, 2)}
    <path d="M56 86 L86 92 M144 86 L114 92" ${t(5)}/>
    <circle cx="92" cy="126" r="3" fill="${O}"/><circle cx="108" cy="126" r="3" fill="${O}"/>
    ${bouche('iguane', 100, 138, 26, { dents: true })}
    ${joues(60, 140, 124)}`) };

A.tortue = { nom: 'Tortue', theme: 'bet', svg: svg('#9fd4ee', `
    <ellipse cx="30" cy="160" rx="22" ry="11" transform="rotate(-30 30 160)" fill="#8ccf7e" ${T}/>
    <ellipse cx="170" cy="160" rx="22" ry="11" transform="rotate(30 170 160)" fill="#8ccf7e" ${T}/>
    <ellipse cx="100" cy="176" rx="78" ry="48" fill="#a0693a" ${T}/>
    <path d="M60 150 l20 -12 l20 12 l20 -12 l20 12 M40 176 h120 M80 138 v-10 M120 138 v-10 M70 176 l10 -26 M130 176 l-10 -26 M100 176 v-26" fill="none" stroke="#7b4c26" stroke-width="5" stroke-linecap="round"/>
    <circle cx="100" cy="94" r="46" fill="#8ccf7e" ${T}/>
    ${yeux(82, 118, 88, 13, 1, 2)}${joues(72, 128, 110)}${sourire(100, 112, 14)}
    <circle cx="74" cy="64" r="4" fill="#6fb563"/><circle cx="124" cy="62" r="5" fill="#6fb563"/>`) };

A.mangouste = { nom: 'Mangouste', theme: 'bet', svg: svg('#c9e7a6', `
    <circle cx="58" cy="70" r="15" fill="#9c7046" ${T}/><circle cx="142" cy="70" r="15" fill="#9c7046" ${T}/>
    <ellipse cx="100" cy="112" rx="54" ry="58" fill="#b88a5a" ${T}/>
    <path d="M70 84 q8 -8 18 0 M112 84 q10 -8 20 2" fill="none" ${t(4)}/>
    <ellipse cx="100" cy="142" rx="28" ry="22" fill="#e1c39c" ${t(4)}/>
    ${oeil(78, 102, 12, 3, 1)}${oeilRieur(122, 104, 11)}
    <ellipse cx="100" cy="128" rx="10" ry="7" fill="${O}"/>
    <path d="M86 148 q14 12 30 -4" fill="none" ${t(5)}/><path d="M104 150 l3 6 l4 -6" fill="#fff" ${t(3)}/>
    ${joues(66, 134, 124)}`) };

A.crabe = { nom: 'Crabe', theme: 'bet', svg: svg('#ffd9a8', `
    <path d="M44 150 l-22 14 M48 164 l-18 22 M156 150 l22 14 M152 164 l18 22" ${t(7)}/>
    <path d="M52 118 L30 98 M148 118 L170 98" ${t(7)}/>
    <path d="M26 72 a22 22 0 1 0 22 22 l-14 -6 z" fill="#e2523f" ${T}/>
    <path d="M174 72 a22 22 0 1 1 -22 22 l14 -6 z" fill="#e2523f" ${T}/>
    <path d="M82 96 L76 62 M118 96 L124 62" ${t(6)}/>
    <ellipse cx="100" cy="132" rx="66" ry="44" fill="#e2523f" ${T}/>
    ${yeux(76, 124, 58, 15, 1, 2)}
    ${bouche('crabe', 100, 132, 24)}${joues(62, 138, 128, 10, 7)}
    <circle cx="70" cy="108" r="4" fill="#f2826f"/><circle cx="132" cy="106" r="5" fill="#f2826f"/>`) };

A.chatrou = { nom: 'Chatrou', theme: 'bet', svg: svg('#c6c0f2', `
    <g fill="none" stroke-linecap="round">
        ${['M56 126 q-24 24 -12 46 q6 12 -8 18', 'M78 138 q-10 26 2 40 q8 8 0 18', 'M100 140 q4 22 -6 36', 'M122 138 q10 26 -2 40 q-8 8 0 18', 'M144 126 q24 24 12 46 q-6 12 8 18']
            .map((d) => `<path d="${d}" stroke="${O}" stroke-width="24"/><path d="${d}" stroke="#e86fa0" stroke-width="15"/>`).join('')}
    </g>
    <ellipse cx="100" cy="92" rx="58" ry="56" fill="#e86fa0" ${T}/>
    <circle cx="70" cy="62" r="6" fill="#f39cc0"/><circle cx="128" cy="56" r="8" fill="#f39cc0"/><circle cx="140" cy="78" r="5" fill="#f39cc0"/>
    ${yeux(80, 120, 92, 15, 0, 2)}${joues(66, 134, 112)}${boucheO(100, 118, 8)}`) };

A.raton = { nom: 'Raton laveur', theme: 'bet', svg: svg('#f4b9a4', `
    <circle cx="54" cy="66" r="19" fill="#a9a6a2" ${T}/><circle cx="54" cy="66" r="9" fill="#3b3330"/>
    <circle cx="146" cy="66" r="19" fill="#a9a6a2" ${T}/><circle cx="146" cy="66" r="9" fill="#3b3330"/>
    <ellipse cx="100" cy="114" rx="64" ry="54" fill="#bdbab5" ${T}/>
    <path d="M42 106 q30 -26 58 -6 q28 -20 58 6 q-16 24 -36 14 q-12 -4 -22 4 q-10 -8 -22 -4 q-20 10 -36 -14z" fill="#3b3330"/>
    ${yeux(76, 124, 104, 12, 1, 1)}
    <ellipse cx="100" cy="144" rx="30" ry="20" fill="#f2efe9" ${t(4)}/>
    <ellipse cx="100" cy="132" rx="10" ry="7" fill="${O}"/>${sourire(100, 146, 11)}
    <path d="M100 74 v12 M88 76 v8 M112 76 v8" ${t(4)}/>`) };

A.pelican = { nom: 'Pélican', theme: 'bet', svg: svg('#a8dcf0', `
    <path d="M78 98 Q70 160 100 184 Q130 160 122 98 Z" fill="#f29a3c" ${T}/>
    <path d="M86 112 q14 40 28 0" fill="none" stroke="#d8752a" stroke-width="4" stroke-linecap="round"/>
    <circle cx="100" cy="82" r="46" fill="#f6f2ea" ${T}/>
    <path d="M84 40 q8 -16 22 -10 q-8 6 -6 14" fill="#f6f2ea" ${T}/>
    ${yeux(82, 118, 78, 13, 1, 2)}
    <path d="M76 100 Q100 112 124 100 L118 112 Q100 120 82 112 Z" fill="#f7b347" ${t(4)}/>
    ${joues(70, 130, 96, 8, 5)}`) };

// ===== Fwi épi légim : fruits et légumes rigolos =====
A.ananas = { nom: 'Ananas', theme: 'fwi', svg: svg('#b9e2f5', `
    <path d="M72 74 L56 32 L86 60 L100 16 L114 60 L144 32 L128 74 Z" fill="#3f9a4f" ${T}/>
    <clipPath id="ananas-c"><ellipse cx="100" cy="128" rx="50" ry="58"/></clipPath>
    <ellipse cx="100" cy="128" rx="50" ry="58" fill="#f6b53a"/>
    <g clip-path="url(#ananas-c)" stroke="#d98d1f" stroke-width="4">
        ${[-60, -30, 0, 30, 60, 90].map((d) => `<path d="M${40 + d} 60 l90 130 M${160 - d} 60 l-90 130"/>`).join('')}
    </g>
    <ellipse cx="100" cy="128" rx="50" ry="58" fill="none" ${T}/>
    <path d="M58 110 h84" ${t(5)}/>
    <rect x="60" y="100" width="34" height="24" rx="10" fill="${O}"/><rect x="106" y="100" width="34" height="24" rx="10" fill="${O}"/>
    <path d="M68 106 l10 0" stroke="#fff" stroke-width="4" stroke-linecap="round" opacity=".7"/><path d="M114 106 l10 0" stroke="#fff" stroke-width="4" stroke-linecap="round" opacity=".7"/>
    ${sourire(100, 144, 16)}${joues(66, 134, 140)}`) };

A.mangue = { nom: 'Mangue', theme: 'fwi', svg: svg('#ffd0d6', `
    <path d="M108 52 q4 -10 0 -18" fill="none" ${t(5)}/>
    <ellipse cx="122" cy="40" rx="24" ry="10" transform="rotate(-24 122 40)" fill="#3f9a4f" ${T}/>
    <ellipse cx="100" cy="116" rx="60" ry="66" transform="rotate(-18 100 116)" fill="#f7a531" ${T}/>
    <ellipse cx="80" cy="88" rx="34" ry="28" transform="rotate(-30 80 88)" fill="#e5533b" opacity=".75"/>
    <ellipse cx="128" cy="80" rx="8" ry="14" transform="rotate(30 128 80)" fill="#fff" opacity=".45"/>
    ${oeilRieur(82, 112, 12)}${oeilRieur(122, 108, 12)}
    ${bouche('mangue', 104, 130, 18)}${joues(70, 136, 128)}`) };

A.coco = { nom: 'Coco', theme: 'fwi', svg: svg('#ffe1a6', `
    <path d="M120 66 L146 18 L160 22" fill="none" stroke="${O}" stroke-width="12" stroke-linecap="round"/>
    <path d="M120 66 L146 18 L160 22" fill="none" stroke="#e5533b" stroke-width="6" stroke-linecap="round"/>
    <path d="M140 30 q-16 -26 6 -24 q18 -6 24 14 z" fill="#4fb3e0" ${t(4)}/>
    <circle cx="100" cy="116" r="62" fill="#7a4b2a" ${T}/>
    <path d="M52 96 l-8 -4 M58 150 l-8 6 M150 148 l8 6 M148 92 l8 -4 M70 166 l-4 8 M132 166 l4 8" ${t(4)}/>
    <ellipse cx="100" cy="66" rx="44" ry="13" fill="#f7f1e3" ${t(4)}/>
    ${yeux(80, 120, 112, 13, 1, 1)}${joues(68, 132, 134)}
    ${bouche('coco', 100, 136, 16)}`) };

A.christophine = { nom: 'Christophine', theme: 'fwi', svg: svg('#cfe0ff', `
    <path d="M100 44 q-4 -18 10 -22 q12 -2 10 10 q-2 8 -10 4" fill="none" stroke="#3f9a4f" stroke-width="5" stroke-linecap="round"/>
    <path d="M100 42 C150 42 168 92 162 130 C158 164 132 182 100 182 C68 182 42 164 38 130 C32 92 50 42 100 42Z" fill="#a7d36f" ${T}/>
    <path d="M70 60 q-12 40 -6 70 M130 60 q12 40 6 70" fill="none" stroke="#86b955" stroke-width="5" stroke-linecap="round"/>
    <circle cx="60" cy="96" r="3" fill="#6f9e43"/><circle cx="146" cy="104" r="3" fill="#6f9e43"/><circle cx="56" cy="140" r="3" fill="#6f9e43"/><circle cx="148" cy="146" r="3" fill="#6f9e43"/>
    ${sourcil(70, 80, 88, 76)}${sourcil(112, 76, 130, 80)}
    ${yeux(80, 120, 100, 14, 1, 2)}
    ${bouche('christophine', 100, 136, 24, { dents: true })}
    ${joues(64, 136, 128)}
    <path d="M84 172 q16 -10 32 0" fill="none" stroke="#86b955" stroke-width="5" stroke-linecap="round"/>`) };

A.banane = { nom: 'Banane', theme: 'fwi', svg: svg('#ffc4a8', `
    <rect x="92" y="28" width="16" height="22" rx="5" fill="#7a5530" ${T}/>
    <path d="M70 58 Q62 120 72 178 L128 178 Q138 120 130 58 Q100 40 70 58Z" fill="#ffd84a" ${T}/>
    <path d="M72 128 Q50 150 40 182 Q64 170 80 150 Z" fill="#f5c531" ${T}/>
    <path d="M128 128 Q150 150 160 182 Q136 170 120 150 Z" fill="#f5c531" ${T}/>
    <path d="M84 132 Q100 140 116 132 L118 170 L82 170 Z" fill="#fff6d6"/>
    ${yeux(86, 116, 88, 12, 1, 1)}${joues(78, 124, 106, 7, 5)}
    ${bouche('banane', 101, 106, 13)}`) };

A.zaboka = { nom: 'Zaboka', theme: 'fwi', svg: svg('#ffd7a3', `
    <path d="M100 26 C128 26 136 62 150 92 C170 132 154 182 100 182 C46 182 30 132 50 92 C64 62 72 26 100 26Z" fill="#3d7a3a" ${T}/>
    <path d="M100 40 C122 40 128 70 140 96 C156 130 144 170 100 170 C56 170 44 130 60 96 C72 70 78 40 100 40Z" fill="#d4e98f"/>
    <circle cx="100" cy="138" r="24" fill="#8a5a34" ${T}/>
    <ellipse cx="92" cy="130" rx="6" ry="8" fill="#b07a4a"/>
    ${yeux(86, 114, 86, 10, 1, 1)}${joues(78, 122, 102, 6, 4)}${sourire(100, 100, 9)}`) };

A.piment = { nom: 'Piment', theme: 'fwi', svg: svg('#ffe7a0', `
    <path d="M98 50 q-2 -18 14 -24" fill="none" stroke="#3f9a4f" stroke-width="8" stroke-linecap="round"/>
    <path d="M70 56 Q100 40 132 56 L128 64 Q100 54 74 64 Z" fill="#3f9a4f" ${T}/>
    <path d="M72 62 C36 96 50 160 126 188 C112 160 154 112 130 64 C114 52 86 52 72 62Z" fill="#e0362c" ${T}/>
    <ellipse cx="86" cy="84" rx="7" ry="14" transform="rotate(20 86 84)" fill="#fff" opacity=".4"/>
    ${sourcil(74, 86, 92, 94)}${sourcil(108, 94, 126, 86)}
    ${yeux(86, 114, 104, 11, 0, 2)}
    ${bouche('piment', 100, 126, 16, { dents: true, langue: false })}
    <path d="M150 74 q8 10 0 16 q-8 -6 0 -16z" fill="#7fd0f2" ${t(3)}/>
    <path d="M38 36 q-6 -10 2 -16 M52 30 q-6 -10 2 -16" fill="none" stroke="#e5533b" stroke-width="4" stroke-linecap="round"/>`) };

A.pasteque = { nom: 'Pastèque', theme: 'fwi', svg: svg('#c9ecf7', `
    <path d="M18 72 A82 82 0 0 0 182 72 Z" fill="#3f9a4f" ${T}/>
    <path d="M28 72 A72 72 0 0 0 172 72 Z" fill="#e9f5d0"/>
    <path d="M36 72 A64 64 0 0 0 164 72 Z" fill="#f0545a"/>
    <path d="M18 72 H182" ${T}/>
    ${[[60, 116], [140, 116], [100, 128], [78, 134], [122, 134], [48, 92], [152, 92]].map(([x, y]) => `<ellipse cx="${x}" cy="${y}" rx="3.5" ry="6" fill="${O}"/>`).join('')}
    ${yeux(78, 122, 92, 12, 1, 1)}${joues(62, 138, 106, 8, 5)}
    ${bouche('pasteque', 100, 102, 14)}
    <rect x="70" y="40" width="60" height="10" rx="5" fill="#fff" opacity=".0"/>`) };

// ===== Sinéma : objets du cinéma =====
A.popcorn = { nom: 'Pop-corn', theme: 'sinema', svg: svg('#8fd3ff', `
    ${[[64, 82, 18], [88, 66, 20], [114, 66, 20], [138, 82, 18], [100, 52, 16], [74, 58, 14], [128, 56, 14]].map(([x, y, r]) => `<circle cx="${x}" cy="${y}" r="${r}" fill="#fff3c9" ${t(4)}/>`).join('')}
    <circle cx="94" cy="58" r="4" fill="#f6c35b"/><circle cx="130" cy="80" r="4" fill="#f6c35b"/>
    <clipPath id="popcorn-c"><path d="M48 90 L152 90 L138 186 L62 186 Z"/></clipPath>
    <path d="M48 90 L152 90 L138 186 L62 186 Z" fill="#fff"/>
    <g clip-path="url(#popcorn-c)" fill="#e2433a">${[0, 1, 2, 3, 4].map((i) => `<rect x="${44 + i * 24}" y="80" width="12" height="120"/>`).join('')}</g>
    <path d="M48 90 L152 90 L138 186 L62 186 Z" fill="none" ${T}/>
    <rect x="68" y="108" width="64" height="58" rx="18" fill="#fff" ${t(4)}/>
    ${yeux(86, 114, 126, 10, 1, 1)}${sourire(100, 146, 11)}${joues(76, 124, 144, 6, 4)}`) };

A.clap = { nom: 'Clap', theme: 'sinema', svg: svg('#ffd27a', `
    <g transform="rotate(-16 42 78)">
        <rect x="42" y="56" width="120" height="22" rx="4" fill="#fff" ${T}/>
        ${[0, 1, 2, 3].map((i) => `<path d="M${54 + i * 30} 58 l14 0 l-12 18 l-14 0 z" fill="${O}"/>`).join('')}
    </g>
    <rect x="40" y="80" width="120" height="96" rx="10" fill="#2f2f37" ${T}/>
    <rect x="40" y="80" width="120" height="20" fill="#fff" ${t(4)}/>
    ${[0, 1, 2, 3].map((i) => `<path d="M${52 + i * 30} 81 l14 0 l-12 18 l-14 0 z" fill="${O}"/>`).join('')}
    <circle cx="42" cy="78" r="6" fill="#f2b53a" ${t(3)}/>
    ${yeux(80, 120, 126, 14, 1, 1)}
    ${bouche('clap', 100, 148, 16)}
    <ellipse cx="62" cy="148" rx="8" ry="5" fill="#ff6f86" opacity=".6"/><ellipse cx="138" cy="148" rx="8" ry="5" fill="#ff6f86" opacity=".6"/>
    <path d="M166 26 l4 10 l10 4 l-10 4 l-4 10 l-4 -10 l-10 -4 l10 -4z" fill="#fff"/>`) };

A.bobine = { nom: 'Bobine', theme: 'sinema', svg: svg('#c3b6ef', `
    <path d="M140 150 Q170 176 196 168 L196 196 Q160 200 128 166 Z" fill="#4a3426" ${t(4)}/>
    ${[0, 1, 2, 3].map((i) => `<rect x="${150 + i * 12}" y="${172 + i * 2}" width="6" height="6" fill="#c3b6ef"/>`).join('')}
    <circle cx="100" cy="104" r="68" fill="#aab3bd" ${T}/>
    <circle cx="100" cy="104" r="56" fill="none" stroke="#8b949e" stroke-width="5"/>
    ${[[100, 52], [149, 88], [130, 146], [70, 146], [51, 88]].map(([x, y]) => `<circle cx="${x}" cy="${y}" r="11" fill="#5b636c"/>`).join('')}
    <circle cx="100" cy="106" r="34" fill="#cfd5dc" ${t(4)}/>
    ${yeux(88, 112, 100, 9, 1, 1)}${sourire(100, 116, 10)}${joues(80, 120, 114, 5, 3)}`) };

A.ticket = { nom: 'Ticket', theme: 'sinema', svg: svg('#a8e6cf', `
    <g transform="rotate(-10 100 104)">
        <path d="M30 60 H170 V88 A16 16 0 0 0 170 120 V148 H30 V120 A16 16 0 0 0 30 88 Z" fill="#f2b53a" ${T}/>
        <path d="M140 64 V144" stroke="${O}" stroke-width="4" stroke-dasharray="6 7"/>
        <circle cx="156" cy="104" r="7" fill="#e2433a"/>
        ${yeux(72, 108, 96, 12, 1, 1)}${bouche('ticket', 90, 118, 15)}${joues(58, 122, 114, 7, 5)}
    </g>`) };

A.camera = { nom: 'Caméra', theme: 'sinema', svg: svg('#ffc9a0', `
    <circle cx="72" cy="66" r="25" fill="#4a4f63" ${T}/><circle cx="72" cy="66" r="7" fill="#c9ced8"/>
    <circle cx="124" cy="62" r="25" fill="#4a4f63" ${T}/><circle cx="124" cy="62" r="7" fill="#c9ced8"/>
    <path d="M72 50 v-6 M72 82 v6 M56 66 h-6 M124 46 v-6 M124 78 v6 M140 62 h6" ${t(3)}/>
    <path d="M140 110 L176 94 L176 158 L140 142 Z" fill="#2f3342" ${T}/>
    <rect x="34" y="92" width="110" height="72" rx="14" fill="#5d6378" ${T}/>
    <circle cx="160" cy="80" r="0"/>
    <rect x="54" y="104" width="72" height="48" rx="14" fill="#fff" ${t(4)}/>
    ${yeux(76, 104, 122, 10, 1, 1)}${sourire(90, 138, 10)}
    <circle cx="46" cy="102" r="4" fill="#e2433a"/>`) };

A.soda = { nom: 'Soda', theme: 'sinema', svg: svg('#ffb3c1', `
    <path d="M108 74 L118 28 L140 22" fill="none" stroke="${O}" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M108 74 L118 28 L140 22" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="7 7"/>
    <path d="M58 80 L142 80 L130 184 L70 184 Z" fill="#4f8fd6" ${T}/>
    <path d="M62 110 L138 110 L135 136 L65 136 Z" fill="#fff" opacity=".9"/>
    <ellipse cx="100" cy="80" rx="46" ry="11" fill="#fff" ${T}/>
    ${yeux(84, 116, 122, 11, 1, 1)}${bouche('soda', 100, 148, 14)}${joues(74, 126, 142, 7, 5)}`) };

// ===== Fantézi : personnages fantaisie =====
A.robot = { nom: 'Robot', theme: 'fantezi', svg: svg('#ffd27a', `
    <path d="M100 58 V30" ${t(5)}/><circle cx="100" cy="26" r="9" fill="#e2433a" ${t(4)}/>
    <rect x="30" y="94" width="18" height="34" rx="6" fill="#7f95aa" ${T}/><rect x="152" y="94" width="18" height="34" rx="6" fill="#7f95aa" ${T}/>
    <rect x="44" y="58" width="112" height="104" rx="24" fill="#a9bdd0" ${T}/>
    <rect x="58" y="80" width="84" height="44" rx="18" fill="#2f3a4a"/>
    <circle cx="80" cy="102" r="12" fill="#7ff0e0"/><circle cx="120" cy="102" r="12" fill="#7ff0e0"/>
    <circle cx="83" cy="99" r="4" fill="#fff"/><circle cx="123" cy="99" r="4" fill="#fff"/>
    <rect x="72" y="134" width="56" height="16" rx="6" fill="#fff" ${t(4)}/>
    <path d="M86 134 v16 M100 134 v16 M114 134 v16" ${t(3)}/>
    <circle cx="56" cy="70" r="3.5" fill="#6d8197"/><circle cx="144" cy="70" r="3.5" fill="#6d8197"/><circle cx="56" cy="150" r="3.5" fill="#6d8197"/><circle cx="144" cy="150" r="3.5" fill="#6d8197"/>`) };

A.extraterrestre = { nom: 'Extraterrestre', theme: 'fantezi', svg: svg('#3d3a78', `
    <circle cx="30" cy="40" r="2.5" fill="#fff"/><circle cx="170" cy="30" r="3" fill="#fff"/><circle cx="178" cy="150" r="2" fill="#fff"/><circle cx="20" cy="160" r="2.5" fill="#fff"/>
    <path d="M74 64 L58 30 M126 64 L142 30" ${t(5)}/>
    <circle cx="58" cy="28" r="9" fill="#f2b53a" ${t(4)}/><circle cx="142" cy="28" r="9" fill="#f2b53a" ${t(4)}/>
    <ellipse cx="100" cy="116" rx="60" ry="64" fill="#8be07a" ${T}/>
    ${oeil(70, 108, 13, 1, 1)}${oeil(100, 90, 16, 0, 2)}${oeil(130, 108, 13, -1, 1)}
    ${bouche('alien', 100, 142, 22)}${joues(62, 138, 134)}`) };

A.monstre = { nom: 'Monstre gentil', theme: 'fantezi', svg: svg('#ffcfdf', `
    <path d="M62 62 L54 30 L80 52 Z M138 62 L146 30 L120 52 Z" fill="#fff3d6" ${T}/>
    <path d="M100 40 C150 40 170 80 168 120 C166 168 136 184 100 184 C64 184 34 168 32 120 C30 80 50 40 100 40Z" fill="#4fa3e0" ${T}/>
    <path d="M40 92 l-10 -6 M38 120 l-12 0 M160 92 l10 -6 M162 120 l12 0" ${t(4)}/>
    ${oeil(100, 94, 26, 2, 3)}
    <path d="M64 136 Q100 176 136 136 Z" fill="#5a1620" ${T}/>
    <path d="M78 140 l6 12 l6 -10 M110 142 l6 10 l6 -12" fill="#fff" ${t(3)}/>
    ${joues(56, 144, 126, 10, 7)}`) };

A.pirate = { nom: 'Pirate', theme: 'fantezi', svg: svg('#9fd4ee', `
    <circle cx="100" cy="118" r="58" fill="#b97a4f" ${T}/>
    <path d="M42 108 Q44 52 100 50 Q156 52 158 108 Q100 86 42 108Z" fill="#d93a33" ${T}/>
    <path d="M156 92 q22 -2 26 14 q-16 -2 -22 10 M158 98 q18 10 14 28" fill="#d93a33" ${T}/>
    <circle cx="74" cy="72" r="4" fill="#fff"/><circle cx="98" cy="64" r="4" fill="#fff"/><circle cx="124" cy="68" r="4" fill="#fff"/><circle cx="142" cy="82" r="4" fill="#fff"/>
    <path d="M48 104 L146 128" ${t(4)}/>
    <ellipse cx="78" cy="118" rx="15" ry="13" fill="${O}"/>
    ${oeil(124, 116, 12, 1, 1)}${sourcil(112, 98, 134, 96)}
    <path d="M76 142 Q100 168 126 142 Z" fill="#5a1620" ${T}/>
    <rect x="106" y="143" width="9" height="9" fill="#f2c12e"/>
    <circle cx="158" cy="146" r="8" fill="none" stroke="#f2c12e" stroke-width="5"/>
    ${joues(68, 136, 140, 8, 5)}`) };

A.fantome = { nom: 'Fantôme', theme: 'fantezi', svg: svg('#9a8ce0', `
    <path d="M50 184 L50 100 A50 52 0 0 1 150 100 L150 184 L133 170 L117 184 L100 170 L83 184 L67 170 Z" fill="#fbfaf7" ${T}/>
    <ellipse cx="82" cy="104" rx="10" ry="14" fill="${O}"/><ellipse cx="118" cy="104" rx="10" ry="14" fill="${O}"/>
    <circle cx="85" cy="99" r="3.5" fill="#fff"/><circle cx="121" cy="99" r="3.5" fill="#fff"/>
    ${boucheO(100, 134, 9)}${joues(70, 130, 124, 9, 6)}
    <path d="M40 120 q-16 -6 -18 -22 M160 120 q16 -6 18 -22" fill="none" ${t(5)}/>`) };

A.dragon = { nom: 'Dragon', theme: 'fantezi', svg: svg('#ffe08a', `
    <path d="M62 74 L48 34 L82 64 Z M138 74 L152 34 L118 64 Z" fill="#fff3d6" ${T}/>
    <path d="M38 98 L14 84 L30 118 Z M162 98 L186 84 L170 118 Z" fill="#c95a2a" ${T}/>
    <ellipse cx="100" cy="118" rx="64" ry="54" fill="#e8743b" ${T}/>
    <ellipse cx="100" cy="148" rx="40" ry="22" fill="#f6b27a"/>
    <path d="M80 70 l6 12 l6 -10 l8 12 l8 -12 l6 10 l6 -12" fill="none" stroke="#c95a2a" stroke-width="4" stroke-linecap="round"/>
    ${yeux(76, 124, 108, 14, 1, 2)}${sourcil(62, 90, 86, 94)}${sourcil(114, 94, 138, 90)}
    <circle cx="90" cy="134" r="3.5" fill="${O}"/><circle cx="110" cy="134" r="3.5" fill="${O}"/>
    <path d="M74 146 Q100 168 126 146" fill="none" ${t(5)}/>
    <path d="M84 150 l4 8 l4 -6 M108 152 l4 6 l4 -8" fill="#fff" ${t(3)}/>
    <circle cx="160" cy="44" r="8" fill="#e8e1d7" opacity=".9"/><circle cx="172" cy="30" r="6" fill="#e8e1d7" opacity=".8"/>`) };

A.astronaute = { nom: 'Astronaute', theme: 'fantezi', svg: svg('#1f3a6b', `
    <circle cx="28" cy="34" r="3" fill="#fff"/><circle cx="172" cy="26" r="2.5" fill="#fff"/><circle cx="182" cy="120" r="2" fill="#fff"/><circle cx="16" cy="140" r="2.5" fill="#fff"/><circle cx="150" cy="178" r="2" fill="#fff"/>
    <rect x="22" y="94" width="14" height="30" rx="6" fill="#d9dde3" ${T}/><rect x="164" y="94" width="14" height="30" rx="6" fill="#d9dde3" ${T}/>
    <circle cx="100" cy="108" r="68" fill="#f3f3f3" ${T}/>
    <rect x="48" y="66" width="104" height="86" rx="40" fill="#2b3a55" ${t(4)}/>
    <ellipse cx="100" cy="112" rx="40" ry="38" fill="#c98a5e"/>
    <path d="M64 92 Q100 58 136 92 Q118 80 100 84 Q82 80 64 92Z" fill="#3a2418"/>
    ${yeux(84, 116, 108, 11, 1, 1)}${sourire(100, 128, 12)}${joues(72, 128, 124, 7, 5)}
    <path d="M60 80 q8 -8 18 -10" stroke="#fff" stroke-width="5" stroke-linecap="round" opacity=".6" fill="none"/>`) };

A.ninja = { nom: 'Ninja', theme: 'fantezi', svg: svg('#f7a8a8', `
    <path d="M150 84 Q178 70 186 86 Q172 86 166 96 M152 92 Q180 100 182 120 Q168 108 156 104" fill="#d93a33" ${T}/>
    <circle cx="100" cy="112" r="62" fill="#2d2d3a" ${T}/>
    <path d="M42 84 Q100 70 158 84 L156 94 Q100 80 44 94 Z" fill="#d93a33"/>
    <rect x="50" y="96" width="100" height="38" rx="19" fill="#e3b48a"/>
    ${yeux(80, 120, 115, 11, 1, 1)}${sourcil(66, 102, 90, 108)}${sourcil(110, 108, 134, 102)}
    <path d="M86 152 q14 9 28 0" fill="none" stroke="#5a5a70" stroke-width="4" stroke-linecap="round"/>`) };

// ---------------- Thèmes (ordre d'affichage) ----------------
const themes = [
    { cle: 'bet', label: 'Bèt péyi', texte: 'Les animaux des Antilles' },
    { cle: 'fwi', label: 'Fwi épi légim', texte: 'Fruits et légumes rigolos' },
    { cle: 'sinema', label: 'Sinéma', texte: 'Les objets du cinéma' },
    { cle: 'fantezi', label: 'Fantézi', texte: 'Personnages de fantaisie' },
];

mkdirSync(sortie, { recursive: true });
for (const [cle, a] of Object.entries(A)) writeFileSync(resolve(sortie, `${cle}.svg`), a.svg.replace(/\n\s*/g, ''));
const manifeste = {
    genere_par: 'resources/avatars/avatars.mjs',
    themes: themes.map((th) => ({ ...th, avatars: Object.entries(A).filter(([, a]) => a.theme === th.cle).map(([cle, a]) => ({ cle, nom: a.nom })) })),
};
writeFileSync(resolve(sortie, 'avatars.json'), JSON.stringify(manifeste, null, 2) + '\n');
console.log(`${Object.keys(A).length} avatars -> ${sortie}`);

// Planche d'aperçu (PNG) pour vérifier le rendu : docs/captures/avatars-planche.png
if (process.argv.includes('--planche')) {
    const sharp = (await import('sharp')).default;
    const cles = Object.keys(A);
    const col = 8;
    const taille = 160;
    const lignes = Math.ceil(cles.length / col);
    const composites = await Promise.all(cles.map(async (cle, i) => ({
        input: await sharp(Buffer.from(A[cle].svg)).resize(taille, taille).png().toBuffer(),
        left: (i % col) * (taille + 10) + 10,
        top: Math.floor(i / col) * (taille + 10) + 10,
    })));
    await sharp({ create: { width: col * (taille + 10) + 10, height: lignes * (taille + 10) + 10, channels: 4, background: '#160e0b' } })
        .composite(composites).png().toFile(resolve(ici, '../../docs/captures/avatars-planche.png'));
    console.log('Planche : docs/captures/avatars-planche.png');
}
