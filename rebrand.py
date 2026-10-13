import re, sys, io

base = r"c:\laragon\www\open-se-same-v2"
with open(rf"{base}\rebrand_filelist.txt", encoding="utf-8") as fh:
    files = [l.strip() for l in fh if l.strip()]

# Ordered (most specific / longest first) replacement rules.
rules = [
    (r"MUSE CLOSETS", "OPEN SE SAME"),
    (r"Muse Closets", "Open Se Same"),
    (r"muse closets", "open se same"),
    (r"Muse Colosets", "Open Se Same"),  # typo in source data
    (r"Muse Closet(?!s)", "Open Se Same"),  # singular, not already consumed
    (r"Muse-Closets", "Open-Se-Same"),
    (r"muse-closets", "open-se-same"),
    (r"muse_closets", "open_se_same"),
    (r"MuseClosets", "OpenSeSame"),
    (r"museclosets", "opensesame"),
]

changed = []
for rel in files:
    path = f"{base}\\{rel}"
    try:
        with open(path, "r", encoding="utf-8-sig") as fh:
            content = fh.read()
    except FileNotFoundError:
        print(f"MISSING: {rel}")
        continue
    original = content
    for pattern, repl in rules:
        content = re.sub(pattern, repl, content)
    if content != original:
        with open(path, "w", encoding="utf-8", newline="") as fh:
            fh.write(content)
        changed.append(rel)

print(f"Changed {len(changed)} files")
for c in changed:
    print(" -", c)
