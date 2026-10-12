"""Tests navigateur GADÉ : Mode TV (télécommande), lien « Sur la télé », PWA (SW, pré-cache, hors ligne).

    python tests/Navigateur/tv_pwa.py          (Chrome installé ; http://gade.test)

Se connecte avec GADE_COMPTE_EMAIL / GADE_COMPTE_PASSWORD du .env (jamais affichés), choisit un profil,
puis écrit des captures dans docs/captures/ (tv-*.png, pwa-*.png).
"""
import re
import sys
import pathlib
sys.stdout.reconfigure(encoding="utf-8")
from playwright.sync_api import sync_playwright

ROOT = pathlib.Path(__file__).resolve().parents[2]
import os
BASE = os.environ.get("GADE_BASE", "http://gade.test")
CAP = ROOT / "docs" / "captures"
CAP.mkdir(parents=True, exist_ok=True)
env = {}
for l in (ROOT / ".env").read_text(encoding="utf-8").splitlines():
    if "=" in l and not l.strip().startswith("#"):
        k, v = l.split("=", 1)
        env[k.strip()] = v.strip().strip('"').strip("'")
EMAIL, MDP = env.get("GADE_COMPTE_EMAIL"), env.get("GADE_COMPTE_PASSWORD")
ok, ko = [], []


def check(nom, cond, info=""):
    (ok if cond else ko).append(nom)
    print(("OK  " if cond else "KO  ") + nom + (f"  ({info})" if info and not cond else ""))


def connexion(page):
    page.goto(BASE + "/connexion", wait_until="domcontentloaded")
    if page.locator('input[type="password"]').count():
        page.fill('input[name="email"]', EMAIL)
        page.fill('input[type="password"]', MDP)  # jamais affiché
        with page.expect_navigation():
            page.locator('form:has(input[type="password"]) [type="submit"]').first.click()
        page.wait_for_load_state("domcontentloaded")
    if "/profils" not in page.url:
        page.goto(BASE + "/profils", wait_until="domcontentloaded")
    if "/profils" in page.url:
        choix = page.locator('form[action*="/choisir"]')
        prefere = choix.filter(has_text="Jade")
        cible = prefere.first if prefere.count() else choix.last
        with page.expect_navigation():
            cible.locator('button, [type="submit"]').first.click()
        page.wait_for_load_state("domcontentloaded")
    print("  connecté, page :", page.url)
    if "/connexion" in page.url:
        print("  ÉCHEC connexion :", page.locator("main").inner_text()[:200].replace(chr(10), " | "))
        sys.exit(2)


def focus_info(page):
    return page.evaluate("""() => { const a = document.activeElement; const piste = a?.closest('[data-piste]');
        return { tag: a?.tagName, cls: String(a?.className || ''), href: a?.getAttribute?.('href'),
                 rangee: piste ? [...document.querySelectorAll('[data-piste]')].indexOf(piste) : -1 }; }""")


with sync_playwright() as p:
    b = p.chromium.launch(channel="chrome", headless=True,
                          args=["--unsafely-treat-insecure-origin-as-secure=" + BASE])
    ctx = b.new_context(viewport={"width": 1920, "height": 1080}, locale="fr-FR")
    page = ctx.new_page()
    erreurs = []
    page.on("pageerror", lambda e: erreurs.append(str(e)))
    connexion(page)

    # ---------- Mode TV ----------
    page.goto(BASE + "/?tv=1", wait_until="networkidle")
    check("mode TV activé par ?tv=1", page.evaluate("document.body.classList.contains('mode-tv')"))
    check("mode TV mémorisé (localStorage)", page.evaluate("localStorage.getItem('gade:tv')") == "1")
    for _ in range(14):
        if focus_info(page)["rangee"] >= 0:
            break
        page.keyboard.press("ArrowDown")
        page.wait_for_timeout(120)
    f1 = focus_info(page)
    check("flèche bas → carte d'une rangée", f1["rangee"] >= 0, str(f1))
    page.keyboard.press("ArrowRight"); page.wait_for_timeout(150)
    page.keyboard.press("ArrowRight"); page.wait_for_timeout(150)
    f2 = focus_info(page)
    check("flèche droite → carte suivante, même rangée", f2["rangee"] == f1["rangee"] and f2["href"] != f1["href"], f"{f1} → {f2}")
    page.keyboard.press("ArrowLeft"); page.wait_for_timeout(150)
    f2b = focus_info(page)
    check("flèche gauche → carte précédente", f2b["rangee"] == f1["rangee"] and f2b["href"] != f2["href"])
    page.keyboard.press("ArrowDown"); page.wait_for_timeout(700)
    f3 = focus_info(page)
    check("flèche bas → rangée suivante", f3["rangee"] > f1["rangee"], f"{f2b} → {f3}")
    vis = page.evaluate("() => { const r = document.activeElement.getBoundingClientRect(); return r.top >= 0 && r.bottom <= innerHeight && r.left >= 0 && r.right <= innerWidth; }")
    check("élément focalisé visible (défilement auto)", vis)
    page.screenshot(path=str(CAP / "tv-accueil.png"))
    with page.expect_navigation():
        page.keyboard.press("Enter")
    page.wait_for_load_state("networkidle")
    check("Entrée sur une carte → fiche", "/programme/" in page.url, page.url)
    fiche_url = page.url
    page.wait_for_timeout(400)
    fi = focus_info(page)
    check("focus initial sur Lecture", "btn-gade" in fi["cls"], str(fi))
    page.screenshot(path=str(CAP / "tv-fiche.png"))
    with page.expect_navigation():
        page.keyboard.press("Enter")
    page.wait_for_load_state("domcontentloaded")
    check("Entrée sur Lecture → lecteur", "/regarder/" in page.url, page.url)
    page.wait_for_timeout(900)
    page.screenshot(path=str(CAP / "tv-lecteur.png"))
    with page.expect_navigation():
        page.keyboard.press("Escape")
    page.wait_for_load_state("domcontentloaded")
    page.wait_for_timeout(400)
    check("Retour (Échap) → fiche", page.url.split("?")[0] == fiche_url.split("?")[0], page.url)
    with page.expect_navigation():
        page.keyboard.press("Backspace")
    page.wait_for_load_state("domcontentloaded")
    page.wait_for_timeout(400)
    check("Retour (Backspace) → accueil", "/programme/" not in page.url, page.url)

    # ---------- Sur la télé : lien YouTube ----------
    page.goto(fiche_url, wait_until="networkidle")
    page.locator("[data-tele-ouvrir]").first.click()
    href = page.locator("[data-tele] [data-tele-youtube]:visible").first.get_attribute("href") or ""
    check("fiche : lien YouTube avec ID et t=", bool(re.search(r"youtube\.com/watch\?v=[A-Za-z0-9_-]{11}&t=\d+s$", href)), href)
    check("ordinateur : bouton « Mode TV plein écran »", page.locator("[data-tele-mode-tv]:visible").count() == 1)
    page.wait_for_timeout(300)
    page.screenshot(path=str(CAP / "tv-sur-la-tele-ordinateur.png"))
    page.keyboard.press("Escape")

    lecteur_url = fiche_url.split("?")[0].replace("/programme/", "/regarder/")
    page.goto(lecteur_url + "?t=200", wait_until="domcontentloaded")
    page.wait_for_timeout(1500)
    page.locator("[data-tele-ouvrir]").first.click()
    href2 = page.locator("[data-tele] [data-tele-youtube]:visible").first.get_attribute("href") or ""
    m = re.search(r"[?&]v=([A-Za-z0-9_-]{11})&t=(\d+)s", href2)
    video = page.locator("[data-lecteur]").get_attribute("data-video")
    check("lecteur : lien YouTube = ID de la vidéo + timecode courant", bool(m) and m.group(1) == video and int(m.group(2)) >= 190, href2)
    page.keyboard.press("Escape")
    erreurs_pc = list(erreurs)

    # ---------- Android ----------
    android = b.new_context(viewport={"width": 390, "height": 844}, device_scale_factor=2, is_mobile=True, has_touch=True, locale="fr-FR",
                            user_agent="Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0 Mobile Safari/537.36")
    android.add_cookies(ctx.cookies())
    pa = android.new_page()
    pa.goto(lecteur_url + "?t=300", wait_until="domcontentloaded")
    pa.wait_for_timeout(1500)
    pa.locator("[data-tele-ouvrir]").first.click()
    h = pa.locator("[data-tele] [data-tele-youtube]:visible").first.get_attribute("href") or ""
    check("Android : intent app YouTube avec ID et t=", h.startswith("intent://www.youtube.com/watch?v=" + video) and re.search(r"&t=(29\d|3\d\d)s", h) and "package=com.google.android.youtube" in h, h)
    pa.wait_for_timeout(300)
    pa.screenshot(path=str(CAP / "tv-sur-la-tele-mobile.png"))
    pa.keyboard.press("Escape")
    pa.goto(BASE + "/", wait_until="networkidle")
    if pa.locator(".navbar-toggler").count():
        pa.locator(".navbar-toggler").click()
        pa.wait_for_timeout(500)
    pa.screenshot(path=str(CAP / "pwa-menu-mobile.png"))
    larg = pa.evaluate("document.documentElement.scrollWidth")
    check("mobile : pas de débordement horizontal (menu ouvert)", larg <= 390, str(larg))
    android.close()

    # ---------- PWA ----------
    page.goto(BASE + "/", wait_until="networkidle")
    etat = page.evaluate("""async () => {
        const reg = await Promise.race([navigator.serviceWorker.ready, new Promise(r => setTimeout(() => r(null), 8000))]);
        if (!reg) return null; await new Promise(r => setTimeout(r, 1500));
        const cles = await caches.keys(); const shell = cles.find(c => c.startsWith('gade-shell-'));
        const n = shell ? (await (await caches.open(shell)).keys()).map(r => new URL(r.url).pathname) : [];
        return { actif: !!reg.active, cles, n }; }""")
    check("service worker actif", bool(etat and etat["actif"]), str(etat))
    pre = etat["n"] if etat else []
    check("pré-cache : CSS + JS du build, polices, /hors-ligne, logo",
          any(x.endswith(".css") for x in pre) and any(x.endswith(".js") for x in pre) and any(x.endswith(".woff2") for x in pre)
          and "/hors-ligne" in pre and "/images/brand/logo-gade.svg" in pre, str(pre))
    page.reload(wait_until="networkidle")
    check("le SW contrôle la page", page.evaluate("!!navigator.serviceWorker.controller"))
    check("<link rel=manifest>", bool(page.evaluate("document.querySelector('link[rel=manifest]')?.href")))
    ctx.set_offline(True)
    try:
        page.goto(BASE + "/parcourir", wait_until="domcontentloaded")
    except Exception as e:
        print("  navigation hors ligne :", e)
    texte = page.content()
    check("hors ligne : page « Pa ni rézo »", "Pa ni rézo" in texte and "Il faut Internet pour regarder" in texte)
    page.wait_for_timeout(500)
    page.screenshot(path=str(CAP / "pwa-hors-ligne.png"))
    ctx.set_offline(False)
    fuite = page.evaluate("""async () => { for (const c of await caches.keys()) for (const r of await (await caches.open(c)).keys()) {
        const u = new URL(r.url).pathname; if (u === '/' || /^\\/(programme|regarder|profils|coulisses|ma-liste|moi|parcourir)/.test(u)) return u; } return null; }""")
    check("aucune page de profil en cache", fuite is None, str(fuite))
    check("pas d'erreur JS", not erreurs_pc, "; ".join(erreurs_pc[:3]))
    b.close()

print(f"\n{len(ok)} OK, {len(ko)} KO")
sys.exit(1 if ko else 0)
